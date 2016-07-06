<?php

namespace hypeJunction;

use ElggFile;

/**
 * Attachment Service
 * @access private
 */
class AttachmentService {

	/**
	 * Save uploaded files
	 *
	 * @param string $input_name Form input name
	 * @return ElggFile[]
	 */
	public function saveUploadedFiles($input_name) {

		$files = [];

		$uploaded_files = $this->fixPhpFilesArray($_FILES[$input_name]);

		$keys = array_keys($uploaded_files);
		sort($keys);

		if (self::$fileKeys == $keys) {
			$uploaded_files = [$uploaded_files];
		}

		foreach ($uploaded_files as $uploaded_file) {
			if ($uploaded_file['error'] !== UPLOAD_ERR_OK || empty($uploaded_file['name'])) {
				continue;
			}

			$originalfilename = $uploaded_file['name'];
			$time = time();

			$file = new ElggFile();
			$file->subtype = 'file';
			$file->owner_guid = elgg_get_logged_in_user_guid();
			$file->setFilename("file/{$time}{$originalfilename}");

			$file->open('write');
			$file->close();

			move_uploaded_file($uploaded_file['tmp_name'], $file->getFilenameOnFilestore());

			$file->originalfilename = $originalfilename;
			$file->title = htmlspecialchars($originalfilename, ENT_QUOTES, 'UTF-8');
			$file->access_id = ACCESS_PRIVATE;
			$file->mimetype = $file->detectMimeType($file->getFilenameOnFilestore(), $uploaded_file['type']);
			if (!$file->mimetype) {
				$file->mimetype = $uploaded_file['type'];
			}
			$file->simpletype = file_get_simple_type($file->mimetype);

			if ($file->save()) {
				$this->saveFileIcon($file);
			}

			$files[] = $file;
		}

		return $files;
	}

	/**
	 * Create file icons
	 * 
	 * @param ElggFile $file File entity
	 * @return void
	 */
	function saveFileIcon(ElggFile $file) {
		if ($file->simpletype !== 'image') {
			return;
		}

		$file->icontime = time();

		$filestorename = pathinfo($file->getFilenameOnFilestore(), PATHINFO_BASENAME);

		$thumbnail = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 60, 60, true);
		if ($thumbnail) {
			$thumb = new ElggFile();
			$thumb->setFilename("file/thumb$filestorename");
			$thumb->open("write");
			$thumb->write($thumbnail);
			$thumb->close();
			$file->thumbnail = "file/thumb$filestorename";
			unset($thumbnail);
		}

		$thumbsmall = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 153, 153, true);
		if ($thumbsmall) {
			$thumb->setFilename("file/smallthumb$filestorename");
			$thumb->open("write");
			$thumb->write($thumbsmall);
			$thumb->close();
			$file->smallthumb = "file/smallthumb$filestorename";
			unset($thumbsmall);
		}

		$thumblarge = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 600, 600, false);
		if ($thumblarge) {
			$thumb->setFilename("file/largethumb$filestorename");
			$thumb->open("write");
			$thumb->write($thumblarge);
			$thumb->close();
			$file->largethumb = "file/largethumb$filestorename";
			unset($thumblarge);
		}
	}

	/**
	 * Attach uploaded files for an entity
	 *
	 * @param \ElggEntity $entity     Entity to which the files are attached
	 * @param string      $input_name Form input name
	 * @param array       $attributes Metadata and attributes to set on each uploaded file
	 *                                This can include container_guid, origin etc
	 * @return ElggFile[] GUIDs of attached file entities
	 */
	public function attachUploadedFiles(\ElggEntity $entity, $input_name, array $attributes = []) {

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
	 * Attach attachments to an entity
	 *
	 * @param \ElggEntity $entity     Subject entity
	 * @param ElggFile   $attachment Attachment entity
	 * @return bool
	 */
	public function attach(\ElggEntity $entity, \ElggEntity $attachment) {
		return $entity->addRelationship($attachment->guid, 'attached');
	}

	/**
	 * Detach attachments from entity
	 *
	 * @param \ElggEntity $entity     Subject entity
	 * @param ElggFile   $attachment Attached entity
	 * @param bool        $delete     Also delete attached entities
	 * @return bool
	 */
	public function detach(\ElggEntity $entity, \ElggEntity $attachment, $delete = false) {
		if ($delete) {
			// This will check delete permissions
			return $attachment->delete();
		}

		return $entity->removeRelationship($attachment->guid, 'attached');
	}

	/**
	 * Returns an array of attached entities
	 *
	 * @param \ElggEntity $entity  Subject entity
	 * @param array       $options Additional options
	 * @return \ElggEntity[]|false
	 */
	public function getAttachments(\ElggEntity $entity, array $options = array()) {
		$options = $this->getAttachmentsFilterOptions($entity, $options);
		$attachments = elgg_get_entities_from_relationship($options);
		if ($attachments) {
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
	 * @param \ElggEntity $entity  Subject entity
	 * @param array       $options Additional options
	 * @return int
	 */
	public function hasAttachments(\ElggEntity $entity, array $options = array()) {
		$options['count'] = true;
		return $this->getAttachments($entity, $options);
	}

	/**
	 * Returns getter options for comment attachments
	 *
	 * @param \ElggEntity $entity  Subject entity
	 * @param array       $options Additional options
	 * @return array
	 */
	protected function getAttachmentsFilterOptions(\ElggEntity $entity, array $options = array()) {
		$defaults = array(
			'relationship' => 'attached',
			'relationship_guid' => (int) $entity->guid,
			'inverse_relationship' => false,
		);
		return array_merge($defaults, $options);
	}

	private static $fileKeys = array('error', 'name', 'size', 'tmp_name', 'type');

	/**
	 * http://api.symfony.com/2.3/Symfony/Component/HttpFoundation/FileBag.html
	 * 
	 * Fixes a malformed PHP $_FILES array.
	 *
	 * PHP has a bug that the format of the $_FILES array differs, depending on
	 * whether the uploaded file fields had normal field names or array-like
	 * field names ("normal" vs. "parent[child]").
	 *
	 * This method fixes the array to look like the "normal" $_FILES array.
	 *
	 * It's safe to pass an already converted array, in which case this method
	 * just returns the original array unmodified.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	protected function fixPhpFilesArray($data) {
		if (!is_array($data)) {
			return $data;
		}

		$keys = array_keys($data);
		sort($keys);

		if (self::$fileKeys != $keys || !isset($data['name']) || !is_array($data['name'])) {
			return $data;
		}

		$files = $data;
		foreach (self::$fileKeys as $k) {
			unset($files[$k]);
		}

		foreach ($data['name'] as $key => $name) {
			$files[$key] = $this->fixPhpFilesArray(array(
				'error' => $data['error'][$key],
				'name' => $name,
				'type' => $data['type'][$key],
				'tmp_name' => $data['tmp_name'][$key],
				'size' => $data['size'][$key],
			));
		}

		return $files;
	}

}
