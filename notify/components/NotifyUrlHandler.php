<?php
/**
 * Class NotifyUrlHandler
 */
class NotifyUrlHandler extends CApplicationComponent{
    public $data;
    public function init()
    {

        if(!Yii::app()->user->isGuest){
            $db = Yii::app()->db;
            $user_id=Yii::app()->user->id;
            $dependency = new CDbCacheDependency('SELECT COUNT(id) FROM {{_notify}} user_id='.$user_id);
            $items = $db->cache((60*60*24)*1,$dependency)
                        ->createCommand('SELECT * FROM {{_notify}} t WHERE t.user_id='.$user_id.' AND t.read='.Notify::NOT_READ)
                        ->queryAll();

            foreach ($items as $item)
            {
                if ($item['route'])
                    $this->data[$item['route']][] = array('id'=>$item['id'],'param'=>unserialize($item['route_params']));
            }
            $this->parseUrl();
        }
    }

    public function parseUrl(){
        $read=false;
        $return=false;
        $param=$_GET;
        $route=$param['r'];
        unset($param['r'],$param['lang']);
        if($this->data[$route]){
            $read=true;
            foreach($this->data[$route] as $data){
                if($data['param']){
                    foreach($data['param'] as $k=>$v){
                        $read=$param[$k]==$v;
                    }
                    if($read)
                        $return[]=$data['id'];
                }else{
                    $return[]=$data['id'];
                }
            }

        }
        if($return)
            $this->readNotify($return);
    }

    public function readNotify($array){
        $criteria=new CDbCriteria();
        foreach($array as $id){
            $criteria->addCondition('id='.(int)$id, 'OR');
        }
        Notify::model()->updateAll(array('read'=>Notify::READ),$criteria);
    }


}