<?php
/**
 *
 */
Yii::import('application.modules.notify.models.Notify');
class NotifyComponent extends CApplicationComponent{
    /**
     * if need save only a certain amount of notifications, set the number in this property
     * if don't need delete old notification, please set false this property
     * @var bool
     */
    public $countSaveOld = false;

    /**
     * how soon check for new notifications
     * @var int
     */
    public $timeUpdateCenter = 15000; //milisecond (15 second)

    /**
     * How many notifications will be displayed in a popup window
     * @var int
     */
    public $numberDisplay = 6 ;

    /**
     * how many notifications will be displayed in page where shows all notifications
     * @var int
     */
    public $numberDisplayInAll = 20;

    /**
     * Model name, this name is used in relations
     * @var null
     */
    public $groupModel = null;

    /**
     * Foreign key to Group Model, this name is used in relations
     * @var null
     */
    public $groupModelFk = null;

    /**
     * Field where saved text, that you want display
     * @var null
     */
    public $groupModelFName = null;

    /**
     * This property need get groups ids which saved in users
     * you can set array with ids :
     * [1,2,3,4,5,6,7...]
     * or you can set array with class name and static function which returns array with ids
     * ['Group'=>'GetIds'] it converts to Group::GetIds($user_id)
     * @var null
     */
    public $userGroupIds = null;

    /**
     * Url to default image, you can user special words:
     *  '{themeUrl}' == Yii::app()->theme->baseUrl
     *  '{basePath}' == Yii::app()->basePath
     *  '{baseUrl}'  == Yii::app()->baseUrl
     * @var null
     */
    public $defaultImage = null;

    /**
     * This property need get string with date when user registered
     * you can set string: Y-m-d H:s:i
     * or you can set array with class name and static function which returns array with ids
     * ['Model'=>'Function'] it converts to Model::Function($user_id)
     * @var string null
     */
    public $userDateCreate = null;


    public $forGuest = false;

    public $session_guest_key = 'notify_guest';

    /**
     * @var array
     */
    private $_model;


    /**
     * @param array $options
     * @return array|bool
     */
    public function addNotify($options=[]){
        return Notify::addNotify($options);
    }

    /**
     * Add notification to the user by ID
     * @param $user_id
     * @param array $options
     * @return array|bool
     */
    public function addNotifyForUser($user_id, $options=[]){
        $options['user_id']=$user_id;
        return Notify::addNotify($options);
    }

    /**
     * Add notification for all users
     * @param array $options
     * @return array|bool
     */
    public function addNotifyForAllUsers($options=[]){
        $options['user_id']=Notify::All_USERS;
        return Notify::addNotify($options);
    }

    /**
     * @param $id
     * @param array $options
     * @return array|bool
     */
    public function updateNotifyById($id, $options=[]){
        return Notify::updateNotify($id, $options);
    }

    /**
     * @param $id
     * @return static[]
     */
    public function getNotifyById($id){
        return Notify::getNotify($id);
    }

    /**
     * @param $user_id
     * @return static[]
     */
    public function getNotifyByUserId($user_id){
        return Notify::getNotify(null, $user_id);
    }

    /**
     * @return Notify
     */
    public function create(){
        return new Notify();
    }

    public function getIsShowForGuest(){
        if(!empty($this->messageForGuest)){
            return true;
        }else{
            return false;
        }
    }

    public function getMessageForGuest(){
        if(!empty($this->forGuest)){
            if(is_string($this->forGuest)){
                return Yii::t('translation',$this->forGuest);
            }elseif(is_array($this->forGuest) && count($this->forGuest)>=2){
                return Yii::t($this->forGuest[0],$this->forGuest[1]);
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function getGuestSession(){
        return Yii::app()->session[$this->session_guest_key];
    }

    public function setGuestSession($data){
        Yii::app()->session[$this->session_guest_key] = $data;
    }
}