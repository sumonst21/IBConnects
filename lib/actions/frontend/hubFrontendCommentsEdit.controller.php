<?php

class hubFrontendCommentsEditController extends waJsonController
{
    /**
     * @var hubCommentModel
     */
    protected $model;
    protected $comment_id;

    public function execute()
    {
        $this->model = new hubCommentModel();
        $this->comment_id = waRequest::post('id');
        $comment = $this->model->getById($this->comment_id);

        if (!wa()->getUser()->isAdmin('hub')) {
            if (!$comment || $comment['contact_id'] != $this->getUserId() ||
                (time() - strtotime($comment['datetime']) > 120 * 60)) { // allow editing for 2 hours (smarty, on the other hand, may hide the links earlier)
                throw new waRightsException(_ws('Access denied'));
            }
        }
        $this->save();
    }

    protected function save()
    {
        $text = hubHelper::sanitizeHtml(waRequest::post('text'));
        if (!$text) {
            $this->errors['text'] = _w('Comment text can not be left blank');
            return;
        }

        $this->model->updateById($this->comment_id, array('text' => $text));
    }
}
