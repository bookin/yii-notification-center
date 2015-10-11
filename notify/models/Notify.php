<?php

/**
 * This is the model class for table "{{_notify}}".
 *
 * The followings are the available columns in table '{{_notify}}':
 * @property integer $id
 * @property string $date_show Date notification
 * @property string $date_end
 * @property string $date_create Date create field
 * @property string $header
 * @property string $description
 * @property string $img
 * @property string $url
 * @property integer $user_id
 * @property integer $group_id
 * @property integer $repeat
 * @property integer $type
 * @property integer $read
 *
 * if notification status need change after open him, you can add these two parameters, and check it on page
 * if these parameters match with url on page, this means notification was read
 * @property string $route
 * @property string $route_params
 * @property string $isRead
 *
 * @property NotifyComponent $component
 *
 * @property NotifyStatus $status
 * @property NotifyStatus[] $states
 */
class Notify extends CActiveRecord
{
    /*Прочитаное и не прочитанное*/
    const NOT_READ = 0;
    const READ = 1;
    /*Повторять и не повторять*/
    const NOT_REPEAT = 0;
    const REPEAT = 1;
    /*Всплывающее уведомление*/
    const TYPE_UP = 2;
    const TYPE_DEFAULT = 1;

    const All_USERS = -1;

    protected $_user_groups, $_user_date_create;

    public $maxImageFileSize = 3145728; //3MB
    public $uploadImageFolder = 'upload/notify'; //remember remove ending slash
    public $allowImageType = 'jpg,png,gif';
    public $defineImageSize = [
        'img' => [
            ['alias' => '60x60', 'size' => '60x60'],
        ],
    ];
    protected $_ifSetDefaultImage = true;

    protected $_read_status;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Notify the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{_notify}}';
    }


    protected function beforeSave()
    {
        if (parent::beforeSave()) {
            if ($this->isNewRecord) {
                $this->date_create = date('c');
            }
            if(!$this->date_show){
                $this->date_show = $this->date_create;
            }else{
                $this->date_show = date('c', strtotime($this->date_show));
            }
            if(!$this->img&&$this->_ifSetDefaultImage){
                $this->img=$this->getDefaultImage();
            }
            return true;
        } else
            return false;
    }


    protected function afterSave(){
        parent::afterSave();
        if($this->_read_status != self::NOT_READ && $this->user_id != self::All_USERS){
            $status = new NotifyStatus();
            $status->notify_id = $this->id;
            $status->user_id = $this->user_id;
            $status->read_status = $this->_read_status;
            $status->date_showed = date('c');
            $status->save();
        }
        return true;
    }

    protected function afterDelete(){
        NotifyStatus::model()->deleteAllByAttributes(['notify_id'=>$this->id]);
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['header, description', 'required'],
            ['user_id, group_id','safe'],
            ['date_show, date_end', 'date', 'format'=>'yyyy-M-d H:m:s'],
            ['img', 'file', 'on' => 'upload_image',
                'types' => $this->allowImageType,
                'allowEmpty' => true,
                'maxSize' => $this->maxImageFileSize,
                'tooLarge' => 'The file was larger than ' . ($this->maxImageFileSize / 1024) . ' KB. Please upload a smaller file.',
            ],
        ];
    }

    /**
     * @return NotifyComponent
     */
    public function getComponent(){
        return Yii::app()->notify;
    }

    public function isGroupSupport(){
        return isset($this->component->groupModel)&&isset($this->component->groupModelFk);
    }

    public function relations()
    {
        $relations = [
            'status'=>[self::HAS_ONE, 'NotifyStatus', ['notify_id'=>'id']],
            'states'=>[self::HAS_MANY, 'NotifyStatus', ['notify_id'=>'id']]
        ];
        if($this->isGroupSupport()){
            $relations['group']=[self::HAS_ONE, $this->component->groupModel, [$this->component->groupModelFk=>'group_id']];
        }
        return $relations;
    }

    public function search()
    {
        $criteria=new CDbCriteria();
        $criteria->compare('id', $this->id, true);
        $criteria->compare('header', $this->header, true);
        $criteria->compare('date_show', $this->date_show, true);
        $criteria->compare('date_create', $this->date_create, true);
        if($this->isGroupSupport()){
            $criteria->with = ['group'];
            $criteria->compare('group.id',$this->group_id,true);
        }
        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
            'pagination' => [
                'pageSize' => Yii::app()->params['defaultPageSize'],
            ],
            'sort'=>[
                'defaultOrder'=>'t.id desc',
            ]
        ));
    }

    /**
     * @return array
     */
    public function attributeLabels() {
        return [
            'img' => Yii::t('notify', 'Image'),
            'group_id' => Yii::t('notify', 'Group'),
        ];
    }

    public function getUserGroups($user_id=null){
        if(!$this->_user_groups&&$this->isGroupSupport()){
            $ids = $this->component->userGroupIds;
            if(is_array($ids)){
                if(count($ids)==2&&method_exists($ids[0], $ids[1])){
                    $this->_user_groups = $ids[0]::$ids[1]($user_id);
                }else{
                    $this->_user_groups = $ids;
                }
            }
//            elseif(is_string($ids)){
//                $this->_user_groups = Yii::app()->db->createCommand($ids)->queryAll(true,[':user_id'=>$user_id]);
//            }
        }
        return $this->_user_groups;
    }

    public function getUserDateCreate($user_id=null){
        if(!$this->_user_date_create){
            $date = $this->component->userDateCreate;
            if(!empty($date) && is_string($date) && $date!='0000-00-00' || $date!='0000-00-00 00:00:00'){
                $this->_user_date_create = $date;
            }
            if(!empty($date) && is_array($date)){
                if(count($date)==2&&method_exists($date[0], $date[1])){
                    $this->_user_date_create = $date[0]::$date[1]($user_id);
                }
            }
        }
        return $this->_user_date_create;
    }

    /**
     * @param array $options
     * @return array|bool
     */
    public static function addNotify($options=[]){
        if(isset($options['id'])){
            $model = self::model()->findByPk((int)$options['id']);
        }else{
            $model = new self;
        }
        if(isset($options['url']))
            $model->setUrl($options['url']);
        if(isset($options['image']))
            $model->setImage($options['image']);

        $model->setAttributes($options,false);
        if($model->save()){
            if(isset($options['read']) && (isset($options['user_id']) && (int)$options['user_id'] > 0)){
                self::changeReadStatusById($model->id, $options['user_id'], $options['read']);
            }
            if(isset($options['id'])){
                $model->onUpdateNotify($model);
            }else{
                $model->onAddNotify($model);
            }
            return true;
        }else{
            return $model->getErrors();
        }
    }

    /**
     * @param $id
     * @param array $options
     * @return array|bool
     */
    public static function updateNotify($id,$options = [])
    {
        $options['id']=(int)$id;
        $update = self::addNotify($options);
        return $update;
    }

    /**
     * @param $id
     * @return bool
     */
    public static function deleteNotify($id)
    {
        return (boolean)self::model()->deleteByPk($id);
    }


    public static function deleteOldNotify()
    {
        $criteria = new CDbCriteria;
        $criteria->condition = 't._status = ' . self::READ;
        $criteria->condition = 't.repeat = ' . self::NOT_REPEAT;
        $criteria->order = 't.id desc';
        $criteria->offset = Yii::app()->notify->countSaveOld - 1;
        $criteria->limit = 1;
        if (self::model()->count($criteria)) {
            $subQuery = self::getCommandBuilder()->createFindCommand(self::getTableSchema(), $criteria)->getText();

            $mainCriteria = new CDbCriteria();
            $mainCriteria->condition = ' t.id < (' . $subQuery . ') ';

            if (self::model()->deleteAll($mainCriteria)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $notify_id
     * @param $user_id
     * @param int $status_id
     * @return bool|Notify
     */
    public static function  changeReadStatusById($notify_id, $user_id, $status_id=self::READ){
        $status = NotifyStatus::changeReadStatus($notify_id, $user_id, $status_id);
        return !empty($status->notify_id)?$status->notify_id:$status;
    }

    /**
     * @param array $ids
     * @param $user_id
     * @param int $status_id
     * @return array
     */
    public static function  changeReadStatusByIds($ids=[], $user_id, $status_id=self::READ){
        $return = [];
        if(!Yii::app()->user->isGuest){
            foreach($ids as $notify_id){
                $return[$notify_id]= self::changeReadStatusById($notify_id, $user_id, $status_id);
            }
        }elseif(Yii::app()->notify->isShowForGuest){
            $session = Yii::app()->notify->guestSession;
            if(empty($session['status'])){
                $session['status']=true;
                Yii::app()->notify->guestSession = $session;
            }
//            $return = [$session['id']=>true];
            $return = [$session['id']=>false];
        }
        return $return;
    }

    public static function getCountNew($user_id=null){
        $user_id = isset($user_id)?(int)$user_id:Yii::app()->user->id;
        $model=self::model();
        $criteria = new CDbCriteria;
        $criteria->with=[
            'status'=>[
                'on'=>'status.user_id = :user_id'
            ]
        ];
        $criteria->params = [
            ':user_id' => $user_id,
            ':all_users' => self::All_USERS,
            ':type' => Notify::TYPE_DEFAULT,
            ':today'=>date('c'),
            ':read'=>self::NOT_READ,
            ':user_date_create'=>$model->getUserDateCreate($user_id)
        ];
        $criteria->addCondition('t.user_id=:user_id OR t.user_id=:all_users');
        if($model->isGroupSupport()&&$model->getUserGroups($user_id)){
            $criteria->addInCondition('t.group_id',$model->getUserGroups($user_id),'OR');
        }
        $criteria->addCondition('(t.date_create >= :user_date_create AND t.date_show<=:today) AND IF(date_end, :today <= date_end, 1)');
        $criteria->addCondition('t.type = :type');
        $criteria->addCondition('status.read_status = :read OR status.read_status IS NULL');
        $dependency = new CDbCacheDependency("SELECT COUNT(id) FROM {{_notify}} ");
        return self::model()->cache(60*60*24*5,$dependency)->count($criteria);
    }

    /**
     * @param null $notify_id
     * @param null $user_id
     * @param null $limit integer
     * @return static[]
     */
    public static function getNotify($notify_id=null, $user_id=null, $limit=null)
    {
        $user_id = isset($user_id)?(int)$user_id:Yii::app()->user->id;
        $model=self::model();
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
            ':all_users' => self::All_USERS,
            ':type' => Notify::TYPE_DEFAULT,
            ':today'=>date('c'),
            ':user_date_create'=>$model->getUserDateCreate($user_id)
        ];
        $criteria->addCondition('t.user_id=:user_id OR t.user_id=:all_users');
        if($model->isGroupSupport()&&$model->getUserGroups($user_id)){
            $criteria->addInCondition('t.group_id',$model->getUserGroups($user_id),'OR');
        }
        $criteria->addCondition('(t.date_create >= :user_date_create AND t.date_show<=:today) AND (IF(date_end, :today <= date_end, 1) OR status.user_id IS NOT NULL)');
        $criteria->addCondition('t.type = :type');
        $criteria->order = 'date_showed desc, t.date_show desc';
        $criteria->limit = !empty($limit)&&$limit>Yii::app()->notify->numberDisplay?$limit:Yii::app()->notify->numberDisplay;

        if(isset($notify_id)){
            $criteria->addCondition('t.id=:notify_id');
            $criteria->params['notify_id'] = (int)$notify_id;
        }

        $dependency = new CDbCacheDependency("SELECT COUNT(id) FROM {{_notify}} ");
        $models = self::model()->cache(60*60*24*5,$dependency)->findAll($criteria);
        self::setShowedDate($models, $user_id);
        return $models;
    }

    protected static function setShowedDate($notifies, $user_id){
        if(is_array($notifies)){
            $status = [];
            foreach($notifies as $notify){
                if(empty($notify->status)){
                    $status[] = ['notify_id'=>$notify->id, 'user_id'=>$user_id, 'read_status'=>self::NOT_READ,'date_showed'=>date('c')];
                }
            }
            if($status){
                Yii::app()->db->schema->commandBuilder->createMultipleInsertCommand(NotifyStatus::model()->tableSchema, $status)->execute();
            }
        }
    }

    /**
     * @param $id
     * @return static[]
     */
    public function getNotifyById($id){
        return self::getNotify($id);
    }

    /**
     * @param $user_id
     * @return static[]
     */
    public function getNotifyByUserId($user_id){
        return self::getNotify(null, $user_id);
    }

    /**
     * Возвращаем полный url
     * @param string $route
     * @param $params
     * @return string
     */
    public static function getCreateAbsoluteUrl($route, $params)
    {
        if($params&&!is_array($params))
            $params=unserialize($params);

        if(!empty($route)){
            return Yii::app()->createAbsoluteUrl($route, $params);
        }else{
            return '';
        }
    }

    /**
     * Возвращаем url для xml что бы небыло &
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getCreateXmlUrl($route, $params)
    {
        if(!is_array($params))
            $params=unserialize($params);

        $url=$route.'/'.Yii::app()->urlManager->createPathInfo($params,'/','/');
        return Yii::app()->createAbsoluteUrl($url,array('lang'=>false));
    }


    /**
     * @param string $header
     * @return $this
     */
    public function setHeader($header){
        $this->header=$header;
        return $this;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description){
        $this->description=$description;
        return $this;
    }

    /**
     * You can set false if do not want use default image
     * @param string|bool $url
     * @return $this
     */
    public function setImage($url){
        if($url === false){
            $this->_ifSetDefaultImage = false;
        }elseif($url && $this->checkImageByUrl($url)){
            $this->img=$url;
        }
        return $this;
    }

    /**
     * @param string $size
     * @return string
     */
    public function getImage($size="60x60"){
        if($this->img&&strpos($this->img,'/') === false){
            return $this->getImageUrl('img',$size);
        }elseif($this->img){
            return $this->img;
        }
    }

    /**
     * @return string
     */
    public function getDefaultImage(){
        if(isset($this->component->defaultImage)){
            $replace = [
                '{themeUrl}'=>Yii::app()->theme->baseUrl,
                '{basePath}'=>Yii::app()->basePath,
                '{baseUrl}'=>Yii::app()->baseUrl
            ];
            $url = strtr($this->component->defaultImage, $replace);
            return $this->checkImageByUrl($url)?$url:'';
        }
    }

    /**
     * String or array with Route and Params that can be used to create a URL.
     * See {@link CHtml::createUrl} for more details about how to specify this parameter. - [$route, $params]
     * @param array|string $url
     * @return $this
     */
    public function setUrl($url){
        if(is_array($url) && isset($url[0])){
            $this->route=$url[0];
            $this->route_params=serialize($url[1]);//array_splice($url,1)
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(){
        $params = [];
        if($this->route_params){
            if(is_array($this->route_params))
                $params = $this->route_params;
            else
                $params = unserialize($this->route_params);
        }
        if($this->route)
            return $params ? CHtml::normalizeUrl(Yii::app()->createUrl($this->route, $params)) : CHtml::normalizeUrl([$this->route]);
        else
            return '';
    }

    /**
     * @param int $user_id
     * @return $this
     */
    public function setUserId($user_id){
        $this->user_id=$user_id;
        return $this;
    }

    /**
     * @param int $status
     * @return $this
     */
    public function setReadStatus($status){
        if($status == Notify::READ || $status == Notify::NOT_READ)
            $this->_read_status=$status;
        return $this;
    }

    /**
     * @param string $date
     * @return $this
     */
    public function setDateShow($date){
        $this->date_show = date("c" , strtotime($date));
        return $this;
    }

    /**
     * @return string
     */
    public function getDate(){
        $date = $this->date_show;
        if($date=='0000-00-00' || $date=='0000-00-00 00:00:00' || is_null($date) || empty($date))
            $date = $this->date_create;
        return $date;
    }

    /**
     * @return $this
     */
    public function send(){
        $this->save();
        return $this;
    }

    /**
     * You can check image exists by url (full or relative url)
     * @param $url
     * @return bool
     */
    public static function checkImageByUrl($url){
        $exists = false;
        $preg = preg_match('/^http(s)?:\/\/[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(\/.*)?$/i', $url);
        if($preg){
            $file_headers = @get_headers($url);
            if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
                $exists = false;
            } else {
                $exists = true;
            }
        }else if(strpos($url,'/') !== false){
            $path = $_SERVER['DOCUMENT_ROOT']?$_SERVER['DOCUMENT_ROOT']:Yii::getpathOfAlias('webroot');
            $exists = @file_exists($path.$url);
        }
        return $exists;
    }

    /*
     * Events
     */

    public function onAddNotify($model)
    {
        $event = new CModelEvent($model);
        $this->raiseEvent('onAddNotify', $event);
    }

    public function onUpdateNotify($model)
    {
        $event = new CModelEvent($model);
        $this->raiseEvent('onUpdateNotify', $event);
    }

    public function onDeleteNotify($model)
    {
        $event = new CModelEvent($model);
        $this->raiseEvent('onDeleteNotify', $event);
    }

    public function onDeleteAllNotify($model)
    {
        $event = new CModelEvent($model);
        $this->raiseEvent('onDeleteAllNotify', $event);
    }

    /**
     * @return bool
     */
    public function getIsRead(){
        $status = false;
        if(!Yii::app()->user->isGuest){
            $status = isset($this->status)&&$this->status->read_status? true : false;
        }elseif(Yii::app()->notify->isShowForGuest){
            $session = Yii::app()->notify->guestSession;
            $status = isset($session['status']) && $session['status'] ? true : false;
        }
        return $status;
    }

    /**
     * check exists sent notifications by group_id
     * @param $group_id
     * @param null $user_id
     * @return mixed
     */
    public function isSentByGroup($group_id, $user_id = null){
        $criteria = new CDBCriteria();
        $criteria->params = [];

        $criteria->addCondition('group_id=:group_id');
        $criteria->params[':group_id'] = $group_id;

        if(!empty($user_id)){
            $criteria->addCondition('user_id=:user_id');
            $criteria->params[':user_id'] = $user_id;
        }

        return self::model()->exists($criteria);
    }
    
}