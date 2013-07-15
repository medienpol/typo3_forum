<?php

/*                                                                      *
 *  COPYRIGHT NOTICE                                                    *
 *                                                                      *
 *  (c) 2013 Martin Helmich <m.helmich@mittwald.de>                     *
 *           Sebastian Gieselmann <s.gieselmann@mittwald.de>            *
 *           Ruven Fehling <r.fehling@mittwald.de>                      *
 *           Mittwald CM Service GmbH & Co KG                           *
 *           All rights reserved                                        *
 *                                                                      *
 *  This script is part of the TYPO3 project. The TYPO3 project is      *
 *  free software; you can redistribute it and/or modify                *
 *  it under the terms of the GNU General Public License as published   *
 *  by the Free Software Foundation; either version 2 of the License,   *
 *  or (at your option) any later version.                              *
 *                                                                      *
 *  The GNU General Public License can be found at                      *
 *  http://www.gnu.org/copyleft/gpl.html.                               *
 *                                                                      *
 *  This script is distributed in the hope that it will be useful,      *
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of      *
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the       *
 *  GNU General Public License for more details.                        *
 *                                                                      *
 *  This copyright notice MUST APPEAR in all copies of the script!      *
 *                                                                      */


/**
 *
 * This class implements a simple dispatcher for a mm_form eID script.
 *
 * @author     Martin Helmich <m.helmich@mittwald.de>
 * @author     Sebastian Gieselmann <s.gieselmann@mittwald.de>
 * @author     Ruven Fehling <r.fehling@mittwald.de>
 * @package    MmForum
 * @subpackage Controller
 * @version    $Id$
 *
 * @copyright  2012 Martin Helmich <m.helmich@mittwald.de>
 *             Mittwald CM Service GmbH & Co. KG
 *             http://www.mittwald.de
 * @license    GNU Public License, version 2
 *             http://opensource.org/licenses/gpl-license.php
 *
 */
class Tx_MmForum_Controller_AjaxController extends Tx_MmForum_Controller_AbstractController {




	/*
	 * ATTRIBUTES
	 */


	/**
	 * A forum repository.
	 * @var Tx_MmForum_Domain_Repository_Forum_ForumRepository
	 */
	protected $forumRepository;


	/**
	 * A topic repository.
	 * @var Tx_MmForum_Domain_Repository_Forum_TopicRepository
	 */
	protected $topicRepository;


	/**
	 * A post repository.
	 * @var Tx_MmForum_Domain_Repository_Forum_PostRepository
	 */
	protected $postRepository;


	/**
	 * A post factory.
	 * @var Tx_MmForum_Domain_Factory_Forum_PostFactory
	 */
	protected $postFactory;


	/**
	 * A post factory.
	 * @var Tx_MmForum_Domain_Repository_Forum_AttachmentRepository
	 */
	protected $attachmentRepository;

	/**
	 * @var Tx_MmForum_Service_AttachmentService
	 */
	protected $attachmentService = NULL;

	/**
	 * Constructor. Used primarily for dependency injection.
	 *
	 * @param Tx_MmForum_Domain_Repository_Forum_ForumRepository $forumRepository
	 * @param Tx_MmForum_Domain_Repository_Forum_TopicRepository $topicRepository
	 * @param Tx_MmForum_Domain_Repository_Forum_PostRepository $postRepository
	 * @param Tx_MmForum_Domain_Factory_Forum_PostFactory $postFactory
	 * @param Tx_MmForum_Domain_Repository_Forum_AttachmentRepository $attachmentRepository
	 * @param Tx_MmForum_Service_SessionHandlingService $sessionHandling
	 * @param Tx_MmForum_Service_AttachmentService $attachmentService
	 */
	public function __construct(Tx_MmForum_Domain_Repository_Forum_ForumRepository $forumRepository,
								Tx_MmForum_Domain_Repository_Forum_TopicRepository $topicRepository,
								Tx_MmForum_Domain_Repository_Forum_PostRepository $postRepository,
								Tx_MmForum_Domain_Factory_Forum_PostFactory $postFactory,
								Tx_MmForum_Domain_Repository_Forum_AttachmentRepository $attachmentRepository,
								Tx_MmForum_Service_SessionHandlingService $sessionHandling,
								Tx_MmForum_Service_AttachmentService $attachmentService) {
		$this->forumRepository = $forumRepository;
		$this->topicRepository = $topicRepository;
		$this->postRepository = $postRepository;
		$this->postFactory = $postFactory;
		$this->attachmentRepository = $attachmentRepository;
		$this->sessionHandling		= $sessionHandling;
		$this->attachmentService = $attachmentService;
	}

	//
	// ACTION METHODS
	//

	/**
	 * @param string $displayedUser
	 * @param string $postSummarys
	 * @param string $topicIcons
	 * @param string $forumIcons
	 * @param string $displayedTopics
	 * @param int $displayOnlinebox
	 * @param string $displayedPosts
	 * @return void
	 */
	public function mainAction($displayedUser = "", $postSummarys = "", $topicIcons = "", $forumIcons = "", $displayedTopics = "", $displayOnlinebox = 0, $displayedPosts = "") {
		// json array
		$content = array();
		if (!empty($displayedUser)) {
			$content['onlineUser'] = $this->_getOnlineUser($displayedUser);
		}
		if (!empty($postSummarys)) {
			$content['postSummarys'] = $this->_getPostSummarys($postSummarys);
		}
		if (!empty($topicIcons)) {
			$content['topicIcons'] = $this->_getTopicIcons($topicIcons);
		}
		if (!empty($forumIcons)) {
			$content['forumIcons'] = $this->_getForumIcons($forumIcons);
		}
		if (!empty($displayedTopics)) {
			$content['topics'] = $this->_getTopics($displayedTopics);
		}
		if (!empty($displayedPosts)) {
			$content['posts'] = $this->_getPosts($displayedPosts);
		}
		if($displayOnlinebox == 1){
			$content['onlineBox'] = $this->_getOnlinebox();
		}

		$this->view->assign('content', json_encode($content));
	}


	/**
	 * @return void
	 */
	public function loginboxAction(){

	}

	private function _getOnlinebox(){
		$data = array();
		$data['count'] = $this->frontendUserRepository->countByFilter(TRUE);
		$this->request->setFormat('html');
		$users = $this->frontendUserRepository->findByFilter(intval($this->settings['widgets']['onlinebox']['limit']), array(), TRUE);
		$this->view->assign('users', $users);
		$data['html'] = $this->view->render('Onlinebox');
		$this->request->setFormat('json');
		return $data;
	}
	/**
	 * @param string $displayedTopics
	 * @return array
	 */
	private function _getPosts($displayedPosts){
		$data = array();
		$displayedPosts = json_decode($displayedPosts);
		if(count($displayedPosts) < 1) return $data;
		$this->request->setFormat('html');
		$posts = $this->postRepository->findByUids($displayedPosts);
		$counter = 0;
		foreach($posts as $post){
			$this->view->assign('post', $post)
			->assign('user', $this->getCurrentUser());
			$data[$counter]['uid'] = $post->getUid();
			$data[$counter]['postHelpfulButton'] = $this->view->render('PostHelpfulButton');
			$data[$counter]['postEditLink'] = $this->view->render('PostEditLink');
			$counter ++;
		}
		$this->request->setFormat('json');
		return $data;
	}
	/**
	 * @param string $displayedTopics
	 * @return array
	 */
	private function _getTopics($displayedTopics){
		$data = array();
		$displayedTopics = json_decode($displayedTopics);
		if(count($displayedTopics) < 1) return $data;
		$this->request->setFormat('html');
		$topicIcons = $this->topicRepository->findByUids($displayedTopics);
		$counter = 0;
		foreach($topicIcons as $topic){
			$this->view->assign('topic', $topic);
			$data[$counter]['uid'] = $topic->getUid();
			$data[$counter]['replyCount'] = $topic->getReplyCount();
			$data[$counter]['topicListMenu'] = $this->view->render('topicListMenu');
			$counter ++;
		}
		$this->request->setFormat('json');
		return $data;
	}

	/**
	 * @param string $topicIcons
	 * @return array
	 */
	private function _getTopicIcons($topicIcons){
		$data = array();
		$topicIcons = json_decode($topicIcons);
		if(count($topicIcons) < 1) return $data;
			$this->request->setFormat('html');
		$topicIcons = $this->topicRepository->findByUids($topicIcons);
		$counter = 0;
		foreach($topicIcons as $topic){
			$this->view->assign('topic', $topic);
			$data[$counter]['html'] = $this->view->render('topicIcon');
			$data[$counter]['uid'] = $topic->getUid();
			$counter ++;
		}
		$this->request->setFormat('json');
		return $data;
	}

	/**
	 * @param string $forumIcons
	 * @return array
	 */
	private function _getForumIcons($forumIcons){
		$data = array();
		$forumIcons = json_decode($forumIcons);
		if(count($forumIcons) < 1) return $data;
		$this->request->setFormat('html');
		$forumIcons = $this->forumRepository->findByUids($forumIcons);
		$counter = 0;
		foreach($forumIcons as $forum){
			$this->view->assign('forum', $forum);
			$data[$counter]['html'] = $this->view->render('forumIcon');
			$data[$counter]['uid'] = $forum->getUid();
			$counter ++;
		}
		$this->request->setFormat('json');
		return $data;
	}

	/**
	 * @param string $postSummarys
	 * @return void
	 */
	private function _getPostSummarys($postSummarys) {
		$postSummarys = json_decode($postSummarys);
		$data = array();
		$counter = 0;
		$this->request->setFormat('html');
		foreach($postSummarys as $summary){
			$post = false;
			switch($summary->type){
				case 'lastForumPost':
					$forum  = $this->forumRepository->findByUid($summary->uid);
					/* @var Tx_MmForum_Domain_Model_Forum_Post */
					$post = $forum->getLastPost();
					break;
				case 'lastTopicPost':
					$topic  = $this->topicRepository->findByUid($summary->uid);
					/* @var Tx_MmForum_Domain_Model_Forum_Post */
					$post = $topic->getLastPost();
					break;
			}
			if($post){
				$data[$counter] = $summary;
				$this->view->assign('post', $post)
							->assign('hiddenImage', $summary->hiddenimage);
				$data[$counter]->html = $this->view->render('postSummary');
				$counter ++;
			}
		}
		$this->request->setFormat('json');
		return $data;
	}

	/**
	 * @param array $displayedUser
	 * @return array
	 */
	private function _getOnlineUser($displayedUser) {
		// OnlineUser
		$displayedUser = json_decode($displayedUser);
		$onlineUsers = $this->frontendUserRepository->findByFilter("", array(), true, $displayedUser);
		// write online user
		foreach ($onlineUsers as $onlineUser) {
			$output[] = $onlineUser->getUid();
		}
		if (!empty($output)) return $output;
	}


}