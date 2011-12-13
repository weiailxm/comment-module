<?php

/**
 * Add this behavior to AR Models that are commentable
 * You have to create mapping Table to build the relation in
 * your database. A migration for creating such a table could look like this:
 * <pre>
 * class m111212_030738_add_comment_task_relation extends CDbMigration
 * {
 *     public function up()
 *     {
 *         $this->createTable('tasks_comments_nm', array(
 *             'taskId' => 'bigint(20) unsigned NOT NULL',
 *              'commentId' => 'int',
 *              'PRIMARY KEY(taskId, commentId)',
 *              'KEY `fk_tasks_comments_comments` (`commentId`)',
 *              'KEY `fk_tasks_comments_tasks` (`taskId`)',
 *         ), 'ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
 *
 *         $this->addForeignKey('fk_tasks_comments_comments', 'tasks_comments_nm', 'commentId', 'comments', 'id', 'CASCADE', 'CASCADE');
 *         $this->addForeignKey('fk_tasks_comments_tasks', 'tasks_comments_nm', 'taskId', 'tasks', 'id', 'CASCADE', 'CASCADE');
 *     }
 *
 *     public function down()
 *     {
 *         $this->dropTable('tasks_comments_nm');
 *     }
 * }
 * </pre>
 * In behavio config you have to set {@see $mapTable} to the name of the table
 * and {@see $mapCommentColumn} and {@see $mapRelatedColumn} to the column names you chose.
 * <pre>
 * public function behaviors() {
 *     return array(
 *         'commentable' => array(
 *              'class' => 'ext.comment-module.behaviors.CommentableBehavior',
 *              'mapTable' => 'tasks_comments_nm',
 *              'mapRelatedColumn' => 'taskId'
 *              'mapCommentColumn' => 'commentId'
 *          ),
 *     );
 * }
 * </pre>
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @package yiiext.modules.comment
 */
class CommentableBehavior extends CActiveRecordBehavior
{
	/**
	 * @var string name of the table defining the relation with comment and model
	 */
	public $mapTable = null;
	/**
	 * @var string name of the table column holding commentId in mapTable
	 */
	public $mapCommentColumn = 'commentId';
	/**
	 * @var string name of the table column holding related Objects Id in mapTable
	 */
	public $mapRelatedColumn = null;

	public function attach($owner)
	{
		parent::attach($owner);
		// make sure comment module is loaded so views can be rendered properly
		Yii::app()->getModule('comment');
	}

	public function getCommentInstance()
	{
		$comment = new Comment();
		$types = array_flip(Yii::app()->getModule('comment')->commentableModels);
		if (!isset($types[$c=get_class($this->owner)])) {
			throw new CException('No scope defined in CommentModule for commentable Model ' . $c);
		}
		$comment->setType($types[$c]);
		$comment->setKey($this->owner->primaryKey);
		return $comment;
	}

	public function getComments()
	{
		if (is_null($this->mapTable) || is_null($this->mapRelatedColumn)) {
			throw new CException('mapTable and mapRelatedColumn must not be null!');
		}
		// @todo: add support for composite pks
		return Comment::model()->findAllBySql(
			"SELECT * FROM comments c
			 JOIN " . $this->mapTable . " cm ON c.id = cm." . $this->mapCommentColumn . "
			 WHERE cm." . $this->mapRelatedColumn . "=:pk;", array(':pk'=>$this->owner->getPrimaryKey())
		);
	}

	public function getCommentDataProvider()
	{
		return new CArrayDataProvider($this->getComments());
	}
}
