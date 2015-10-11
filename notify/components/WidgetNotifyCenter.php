<?php
class WidgetNotifyCenter extends CWidget{

    private $module;

    public function init() {
        $this->module=Yii::app()->getModule('notify');
        $this->publishAssets();
    }

    protected function publishAssets() {
        Yii::app()->clientScript->registerScriptFile($this->module->assetsBase.'/jquery.ui.effect-bounce.js');
        Yii::app()->clientScript->registerCssFile($this->module->assetsBase . '/notify_modules.min.css');
        Yii::app()->clientScript->registerScriptFile($this->module->assetsBase.'/notify_modules.min.js');
    }

    public function run() {
        $model=[];
        $count_new=0;

        if(!Yii::app()->user->isGuest){
            $count_new = Notify::getCountNew();
            $model=Notify::getNotify(null, null, $count_new);
        }elseif(Yii::app()->notify->isShowForGuest){
            //unset(Yii::app()->notify->guestSession);
            $session = Yii::app()->notify->guestSession;
            if(!isset($session['id'])){
                $session = ['id'=>'guest_'.rand()];
                Yii::app()->notify->guestSession = $session;
            }
            $count_new = 1;
            /*if(empty($session['status']))
                $count_new = 1;
            else
                $count_new = 0;*/

            $newModel = new Notify();
            $newModel->id = $session['id'];
//            $newModel->header = 'Hello, welcome back!';
            $newModel->description = Yii::app()->notify->messageForGuest;
//            $newModel->date_show = date("Y-m-d H:i:s");
            $newModel->setUrl(['/site/login']);
            $newModel->img=$newModel->getDefaultImage();
            $model=[$newModel];
        }

        if(Yii::app()->user->isGuest){
            $isShowForGuest = Yii::app()->notify->isShowForGuest&&empty(Yii::app()->notify->guestSession['status'])?true:false;
            $isCheckNew = false;
        }else{
            $isShowForGuest = false;
            $isCheckNew = true;
        }

        $this->render('widget',array(
            'model' => $model,
            'count_new'=>$count_new,
            'assetsPath'=>$this->module->publishedUrl,
            'timeUpdateCenter'=>Yii::app()->notify->timeUpdateCenter,
            'notifyUrlUpdate'=>$this->module->notifyUrlUpdate,
            'isCheckNew'=>$isCheckNew,
            'isShowForGuest'=>$isShowForGuest
        ));
    }

    public function getViewPath($checkTheme=false)
    {
      return Yii::getPathOfAlias('notify.views.notify');
    }

}
?>