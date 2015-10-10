<?
/**
 * @var Controller|CWidget $this
 */
?>
<div>
    <?php if(empty($model)){?>
        <div class="no_notify"><?=Yii::t('notify','You have no new notifications')?></div>
    <?php }else{ ?>
        <?php
        foreach($model as $n)
            {
                $params = [
                    'notify'=>$n
                ];
                
                if($this instanceof CController)
                {
                    $this->renderPartial('_notify',$params);
                }else{
                    $this->render('_notify',$params);
                }
            }
        }
    ?>
</div>