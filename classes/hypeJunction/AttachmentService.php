<?php

namespace hypeJunction;

use ElggEntity;
use ElggFile;
use hypeJunction\Attachments\Notifications;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Attachment Service
 *
 * @access private
 */
class AttachmentService {

	/**
	 * @var self
	 */
	static $instance;

	/**
	 * Returns singleton
	 * @return self
	 */
	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Attach uploaded files for an entity
	 *
	 * @param ElggEntity $entity     Entity to which the files are attached
	 * @param string     $input_name Form input name
	 * @param array      $attributes Metadata and attributes to set on each uploaded file
	 *                               This can include container_guid, origin etc
	 * @return ElggFile[] Attached file entities
	 */
	public function attachUploadedFiles(ElggEntity $entity, $input_name, array $attributes = []) {

		// files uploaded via dropzone
		$upload_guids = (array) get_input($input_name, []);

		// files being uploaded via $_FILES
		$uploads = $this->saveUploadedFiles($input_name);
		foreach ($uploads as $upload) {
			if ($upload->guid) {
				$upload_guids[] = $upload->guid;
			}
		}

		$result = [];
		if (empty($upload_guids)) {
			return $result;
		}

		foreach ($upload_guids as $upload_guid) {
			$upload = get_entity($upload_guid);
			if (!$upload) {
				continue;
			}
			foreach ($attributes as $key => $value) {
				$upload->$key = $value;
			}
			if ($upload->save() && $this->attach($entity, $upload)) {
				$result[] = $upload;
			}
		}

		return $result;
	}

	/**
	 * Returns an array of uploaded file objects regardless of upload status/errors
	 *
	 * @param string $input_name Form input name
	 * @return UploadedFile[]
	 */
	protected function getUploadedFiles($input_name) {
		$file_bag = _elgg_services()->request->files;
		if (!$file_bag->has($input_name)) {
			return false;
		}

		$files = $file_bag->get($input_name);
		if (!$files) {
			return [];
		}
		if (!is_array($files)) {
			$files = [$files];
		}
		return array_filter($files);
	}

	/**
	 * Save uploaded files
	 *
	 * @param string $input_name Form input name
	 * @return ElggFile[]
	 */
	protected function saveUploadedFiles($input_name) {

		$files = [];
		
		$uploaded_files = $this->getUploadedFiles($input_name);
		
		if (empty($uploaded_files)) {
			return $files;
		}

		foreach ($uploaded_files as $upload) {
			if (!$upload->isValid()) {
				continue;
			}

			$file = new ElggFile();
			$file->subtype = 'file';
			$file->owner_guid = elgg_get_logged_in_user_guid();

			$old_filestorename = '';
			if ($file->exists()) {
				$old_filestorename = $file->getFilenameOnFilestore();
			}

			$originalfilename = $upload->getClientOriginalName();
			$file->originalfilename = $originalfilename;
			if (empty($file->title)) {
				$file->title = htmlspecialchars($file->originalfilename, ENT_QUOTES, 'UTF-8');
			}

			$file->upload_time = time();
			$prefix = $file->filestore_prefix ? : 'file';
			$prefix = trim($prefix, '/');
			$filename = elgg_strtolower("$prefix/{$file->upload_time}{$file->originalfilename}");
			$file->setFilename($filename);
			$file->filestore_prefix = $prefix;

			$hook_params = [
				'file' => $file,
				'upload' => $upload,
			];

			$uploaded = _elgg_services()->hooks->trigger('upload', 'file', $hook_params);
			if ($uploaded !== true && $uploaded !== false) {
				$filestorename = $file->getFilenameOnFilestore();
				try {
					$uploaded = $upload->move(pathinfo($filestorename, PATHINFO_DIRNAME), pathinfo($filestorename, PATHINFO_BASENAME));
				} catch (FileException $ex) {
					elgg_log($ex->getMessage(), 'ERROR');
					$uploaded = false;
				}
			}

			if (!$uploaded) {
				continue;
			}

			if ($old_filestorename && $old_filestorename != $file->getFilenameOnFilestore()) {
				// remove old file
				unlink($old_filestorename);
			}
			$mime_type = $file->detectMimeType(null, $upload->getClientMimeType());
			$file->setMimeType($mime_type);
			$file->simpletype = elgg_get_file_simple_type($mime_type);
			elgg_trigger_after_event('upload', 'file', $file);

			if (!$file->save() || !$file->exists()) {
				$file->delete();
				continue;
			}

			if ($file->saveIconFromElggFile($file)) {
				$file->thumbnail = $file->getIcon('small')->getFilename();
				$file->smallthumb = $file->getIcon('medium')->getFilename();
				$file->largethumb = $file->getIcon('large')->getFilename();
			}

			$files[] = $file;
		}

		return $files;
	}

	/**
	 * Attach attachments to an entity
	 *
	 * @param ElggEntity $entity     Subject entity
	 * @param ElggFile   $attachment Attachment entity
	 * @return bool
	 */
	public function attach(ElggEntity $entity, ElggEntity $attachment) {
		if (time() - $entity->time_created > 60) {
			// Only notify users if it has been over a minute since the entity was created
			Notifications::registerNotificationHooks($entity);
		}
		return $entity->addRelationship($attachment->guid, 'attached');
	}

	/**
	 * Detach attachments from entity
	 *
	 * @param ElggEntity $entity     Subject entity
	 * @param ElggFile   $attachment Attached entity
	 * @param bool       $delete     Also delete attached entities
	 * @return bool
	 */
	public function detach(ElggEntity $entity, ElggEntity $attachment, $delete = false) {
		if ($delete) {
			// This will check delete permissions
			return $attachment->delete();
		}

		return $entity->removeRelationship($attachment->guid, 'attached');
	}

	/**
	 * Returns an array of attached entities
	 *
	 * @param ElggEntity $entity  Subject entity
	 * @param array      $options Additional options
	 * @return ElggEntity[]|false
	 */
	public function getAttachments(ElggEntity $entity, array $options = []) {
		$options = $this->getAttachmentsFilterOptions($entity, $options);
		$attachments = elgg_get_entities($options);
		if (is_array($attachments)) {
			foreach ($attachments as $attachment) {
				$attachment->setVolatileData('attachment_subject', $entity->guid);
			}
		}
		return $attachments;
	}

	/**
	 * Check if entity has attachments
	 * Returns a count of attachments
	 *
	 * @param ElggEntity $entity  Subject entity
	 * @param array      $options Additional options
	 * @return int
	 */
	public function hasAttachments(ElggEntity $entity, array $options = []) {
		$options['count'] = true;
		return $this->getAttachments($entity, $options);
	}

	/**
	 * Returns getter options for comment attachments
	 *
	 * @param ElggEntity $entity  Subject entity
	 * @param array      $options Additional options
	 * @return array
	 */
	protected function getAttachmentsFilterOptions(ElggEntity $entity, array $options = []) {
		$defaults = [
			'relationship' => 'attached',
			'relationship_guid' => (int) $entity->guid,
			'inverse_relationship' => false,
		];
		return array_merge($defaults, $options);
	}

}
