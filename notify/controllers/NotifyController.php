<?php

class NotifyController extends FrontController
{
    /*public function filters()
    {
        return array(
            'accessControl',
        );
    }

    public function accessRules()
    {
        return array(
            array('allow',
                'users' => array('@'),
            ),
            array('deny',
                'users' => array('?'),
            ),
        );
    }*/

    public function actionAll(){
        $model = Notify::model();
        $user_id = Yii::app()->user->id;
        $criteria = new CDbCriteria;
        $criteria->with=[
            'status'=>[
                'select'=>[
                    'status.id',
                    'status.notify_id',
                    'status.user_id',
                    'status.read_status',
                    'IF(status.date_showed, status.date_showed, "'.date('c').'") as date_showed'
                ],
                'on'=>'status.user_id = :user_id'
            ]
        ];
        $criteria->params = [
            ':user_id' => $user_id,
            ':all_users' => Notify::All_USERS,
            ':type' => Notify::TYPE_DEFAULT,
            ':today'=>date('c'),
            ':user_date_create'=>$model->getUserDateCreate($user_id)
        ];
        $criteria->addCondition('t.user_id=:user_id OR t.user_id=:all_users');
        if($model->isGroupSupport()&&$model->getUserGroups($user_id)){
            $criteria->addInCondition('t.group_id',$model->getUserGroups($user_id),'OR');
        }
        $criteria->addCondition('(t.date_create >= :user_date_create AND t.date_show<=:today) AND  (IF(date_end, :today <= date_end, 1) OR status.user_id IS NOT NULL)');
        $criteria->addCondition('t.type = :type');
        $criteria->order = 'date_showed desc, t.date_show desc';


        $count=Notify::model()->count($criteria);
        $pages=new CPagination($count);
        $pages->pageSize=Yii::app()->notify->numberDisplayInAll;
        $pages->applyLimit($criteria);

        $notifications = Notify::model()->findAll($criteria);
        $ids = array_keys(array_filter(CHtml::listData($notifications, 'id', 'status.read_status'), function($var){
            return $var == 0;
        }));
        if($ids) {
            Notify::changeReadStatusByIds($ids, $user_id, Notify::READ);
            $notifications = Notify::model()->findAll($criteria);
        }

        $this->render('all',[
            'notifications'=>$notifications,
            'pages'=>$pages
        ]);
    }

    public function actionIndex()
    {

        $model = Notify::getNotify();
        $count_new = count(array_filter(CHtml::listData($model, 'id', 'read'), function($var){
            return $var == 0;
        }));

        $module = $this->getModule();
        $this->render('index', [
            'model' => $model,
            'count_new' => $count_new,
            'assetsPath' => $module->publishedUrl,
            'timeUpdateCenter' => $module->timeUpdateCenter
        ]);
    }

    public function actionUpdate()
    {
        header('Content-type: application/json');
        $count_new = Notify::getCountNew();
        $models = Notify::getNotify(null, null, $count_new);
        $count = Yii::app()->request->getParam('count');
        if($count&&$count==$count_new)
            return;
        $module = $this->getModule();
        $content = $this->renderPartial('_content', [
            'model' => $models,
            'count_new' => $count_new,
            'assetsPath' => $module->publishedUrl,
            'timeUpdateCenter' => $module->timeUpdateCenter
        ], true);

        echo json_encode([
            'count' => $count_new,
            'content' => $content
        ]);

        Yii::app()->end();
    }

    public function actionGetUserNotify($email)
    {
        header("Content-Type: text/xml");
        $performer = 9;
        $notify = Notify::model()->findAll(array(
            'condition' => 'performer_id=:performer_id',
            'limit' => '3',
            'params' => array(':performer_id' => $performer)
        ));

        $dom = new domDocument("1.0", "cp1251"); // Создаём XML-документ версии 1.0 с кодировкой utf-8
        $root = $dom->createElement("notifys"); // Создаём корневой элемент
        $dom->appendChild($root);

        foreach ($notify as $n) {
            $notify = $dom->createElement("notify");
            $header = $dom->createElement("header", $n->header);
            $description = $dom->createElement("description", strip_tags($n->description));
            $link = $dom->createElement("link", Notify::getCreateXmlUrl($n->route, $n->route_params));

            $notify->appendChild($header);
            $notify->appendChild($description);
            $notify->appendChild($link);

            $root->appendChild($notify);
        }
        $import = simplexml_import_dom($dom);

        echo $import->asXML();
    }

    public function actionReadNotify(){
        header('Content-type: application/json');
        $ids = Yii::app()->request->getParam('ids');
        $user_id = Yii::app()->request->getParam('user_id');
        $user_id = $user_id ? $user_id : Yii::app()->user->id;
        $respond = [];
        if(is_array($ids)){
            $notifications = Notify::changeReadStatusByIds($ids, $user_id, Notify::READ);
            if($notifications)
            {
                foreach($notifications as $id=>$status){
                    $respond[$id] = !empty($status) ? 1 : 0;
                }
            }
        }
        echo json_encode($respond);

        Yii::app()->end();
    }
}