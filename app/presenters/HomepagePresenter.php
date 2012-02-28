<?php

use \Nette\Application\UI\Form;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter {
	public function renderDefault()	{
		$this->template->repos = $this->db->table('repo');
	}

	protected function createComponentAddRepoForm() {
		$frm = new Form;
		$frm->addProtection();
		$frm->addText('name', 'Name of the project')->setRequired();
		$frm->addText('url', 'GitHub URL')->setRequired();
		$frm->addSubmit('add', 'Generate API');

		$frm->onSuccess[] = callback($this, 'addRepo');

		return $frm;
	}

	public function addRepo(Form $frm) {
		if(!preg_match('~(?:https|git)://github.com/(([^/]+)/([^/]+))(?:\.git)?~Ai', $frm->values['url'], $match)) {
			$frm->addError('Not a valid URL to GitHub repo');
			return;
		}

		$this->db->exec("insert into repo", array(
			'name' => $frm->values['name'],
			'url'  => $frm->values['url'],
			'dir'  => $match[1],
			'added'=> new DateTime,
		));

		$this->flashMessage("Your project has been added. Downloading and generating documentation may take a minute...");
		if(!$this->isAjax()) $this->redirect('default');
	}
}
