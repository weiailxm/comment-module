<?php

/**
 * This module includes complete commenting support into your application
 */
class CommentModule extends CWebModule
{
	/**
	 * @var array associative array of 'scopename' to commentable models 'modelclass'
	 *
	 * 'scopename' must be lower case and is an alias for the model
	 * class name that will be send with the create comment http request.
	 *
	 * 'modelclass' is a class name of the commentable AR
	 * this AR class must have the {@see CommentableBehavior} attached to it
	 */
	public $commentableModels = array('task');

	/**
	 * @var string name of the user model class to use for comments
	 */
	public $userModelClass = 'User';
	/**
	 * @var string attribute which holds the name of the user in {@see $userModelClass}
	 */
	public $userNameAttribute = 'name';
	/**
	 * @var string attribute which holds the email of the user in {@see $userModelClass}
	 */
	public $userEmailAttribute = 'email';


    public function init()
    {
        // this method is called when the module is being created
        // you may place code here to customize the module or the application

        // import the module-level models and components
        $this->setImport(array(
            'comment.models.*',
        ));
    }

    public function beforeControllerAction($controller, $action)
    {
	    // @todo: what to do if user is not loggend in and want to comment?
        if(parent::beforeControllerAction($controller, $action))
        {
            // this method is called before any module controller action is performed
            // you may place customized code here
            return true;
        }
        else
            return false;
    }
}