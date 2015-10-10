<?php
/**
 * Class NotifyModule
 * @property $timeUpdateCenter время проверки оповещений в секундах
 */
class NotifyModule extends CWebModule
{
    // будут сохранены только N последних сообщений
    public $oldSaveCount = 5;

    public $_assetsBase, $_assetsPath;

    public $timeUpdateCenter, $_notifyUrlUpdate;

    public $userModel, $userFieldId, $userEmail;

    //тип всплывающего увидомления
    public $typeNoty = 'information';

    public function init()
    {
        parent::init();
        $this->setImport(array(
            'notify.models.*',
            'notify.components.*',
        ));
    }

    public function beforeControllerAction($controller, $action)
    {
        if (parent::beforeControllerAction($controller, $action)) {
            // this method is called before any module controller action is performed
            // you may place customized code here
            return true;
        } else
            return false;
    }

    //отдаем путь до assets
    public static function getAssetsPath()
    {
        return dirname(__FILE__) . '/assets';
    }

    //отдаем полный путь до папки assets с файлами модуля
    public function getPublishedUrl()
    {
        return Yii::app()->assetManager->getPublishedUrl($this->assetsPath);
    }

    //публикуем файлы из папки assets и возвращаем путь до нее
    public function getAssetsBase()
    {
        if ($this->_assetsBase === null) {
            $assets = $this->assetsPath;
            $this->_assetsBase = Yii::app()->assetManager->publish(
                $assets,
                false,
                -1,
                YII_DEBUG
            );
        }
        return $this->_assetsBase;
    }

    public function getNotifyUrlUpdate(){
        if(!$this->_notifyUrlUpdate){
            return Yii::app()->createUrl('/notify/notify/update');
        }else{
            return $this->_notifyUrlUpdate;
        }
    }



}
