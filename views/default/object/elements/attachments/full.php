<?php

if (elgg_in_context('activity')) {
	return;
}

$attachments = elgg_view('output/attachments', $vars);
if ($attachments) {
	echo elgg_view_module('aside', elgg_echo('attachments:title'), $attachments, [
		'class' => 'sbw-attachments-module',
	]);
}