<?php

/**
 * This is the model class for table "{{_notify_status}}".
 *
 * The followings are the available columns in table '{{_notify_status}}':
 * @property integer $id
 * @property integer $notify_id
 * @property integer $user_id
 * @property integer $read_status
 * @property datetime $date_showed
 *
 * The followings are the available model relations:
 * @property Notify $notify
 */
class NotifyStatus extends CActiveRecord {
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{notify_status}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('notify_id, user_id', 'required'),
			array('notify_id, user_id, read_status', 'numerical', 'integerOnly'=>true),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'notify' => array(self::BELONGS_TO, 'Notify', 'notify_id'),
	    );
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => Yii::t('translation','ID'),
			'notify_id' => Yii::t('translation','Notify'),
			'user_id' => Yii::t('translation','User'),
			'read_status' => Yii::t('translation','Read Status'),
		);
	}


	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return NotifyStatus the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * @param $notify_id
     * @param $user_id
     * @param int $status_id
     * @return bool|NotifyStatus
     */
    public static function changeReadStatus($notify_id, $user_id, $status_id=Notify::READ)
    {
		$notify = Notify::model()->exists('id=:id',['id'=>$notify_id]);
        if(!$notify||(int)$user_id <= 0){
            return false;
        }
        $status = self::model()->findByAttributes(['notify_id'=>$notify_id, 'user_id'=>(int)$user_id]);
        if(!$status){
            $status = new self;
            $status->notify_id = $notify_id;
            $status->user_id = $user_id;
        }
        $status->read_status = ($status_id == Notify::READ || $status_id == Notify::NOT_READ ? $status_id : Notify::READ);
        if ($status->save()) {
            return $status;
        } else {
            return false;
        }

    }
}
