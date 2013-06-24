<?php
class Tx_MmForum_Service_AttachmentService implements \TYPO3\CMS\Core\SingletonInterface {

	/**
	 * Converts HTML-array to an object
	 * @param array $attachments
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	public function initAttachments(array $attachments){
		/* @var Tx_MmForum_Domain_Model_Forum_Attachment */
		$objAttachments = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();

		foreach($attachments AS $attachmentID => $attachment) {
			if($attachment['name'] == '') continue;
			$attachmentObj = new Tx_MmForum_Domain_Model_Forum_Attachment();
			$tmp_name = $_FILES['tx_mmforum_pi1']['tmp_name']['attachments'][$attachmentID];
			$mime_type = mime_content_type($tmp_name);

			//Save in ObjectStorage and in file system
			$attachmentObj->setFilename($attachment['name']);
			$attachmentObj->setRealFilename(sha1($attachment['name'].time()));
			$attachmentObj->setMimeType($mime_type);

			//Create dir if not exists
			$tca = $attachmentObj->getTCAConfig();
			$path = $tca['columns']['real_filename']['config']['uploadfolder'];
			if(!file_exists($path)) {
				mkdir($path,'0777',true);
			}

			//upload file and put in object storage
			$res = \TYPO3\CMS\Core\Utility\GeneralUtility::upload_copy_move($tmp_name,$attachmentObj->getAbsoluteFilename());
			if($res === true) {
				$objAttachments->attach($attachmentObj);
			}
		}
		return $objAttachments;
	}

}