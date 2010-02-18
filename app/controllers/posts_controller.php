<?php

class PostsController extends AppController
{
	var $name = 'Posts';

	function index()
	{
		$this->set('posts', $this->Post->findAll());
	}

	function view($id = null)
	{
		$this->Post->id = $id;
		$this->set('post', $this->Post->read());
	}

	function add()
	{
		if (!empty($this->data))
		{
			if ($this->Post->save($this->data))
			{
				$this->flash('Your post has been saved.','/posts');
			}
		}
	}

	function delete($id)
	{
		$this->Post->id = $id;
		
		$this->data = $this->Post->read();
		
		$this->flash('The post with title: '.$this->data['Post']['title'].' has been deleted.', '/posts');
		$this->Post->del($id);
		
	}

	function edit($id = null)
	{
		if (empty($this->data))
		{
			$this->Post->id = $id;
			$this->data = $this->Post->read();
		}
		else
		{
			if ($this->Post->save($this->data['Post']))
			{
				$this->flash('Your post has been updated.','/posts');
			}
		}
	}

	 
}

?>