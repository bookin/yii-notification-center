<?php
/**
 * @var int $timeUpdateCenter
 * @var string $notifyUrlUpdate
 * @var int $count_new
 * @var bool $isCheckNew
 * @var bool $isShowForGuest
 *
 * @var $clientScript CClientScript
 */
$clientScript = Yii::app()->clientScript;
$script = '
    $.fn.notifyCenter({
        "notifyTimeUpdate":' . $timeUpdateCenter . ',
        "notifyUrlUpdate":"' . $notifyUrlUpdate . '",
        "notifyUrlRead":"' . Yii::app()->createUrl('/notify/notify/readNotify') . '"
    });
';

if($isCheckNew){
    $script.='
        $.fn.notifyCenter("checkNew");
    ';
}

if($isShowForGuest){
    $script .='
        setTimeout(function(){
            $.fn.notifyCenter("show");
        }, 5000);
    ';
}

$clientScript->registerScript('notify_config', $script, CClientScript::POS_END);
?>
<div class="notification_center">
    <a class="notify_button btn <?= ($count_new ? 'active' : '') ?>">
        <span class="glyphicon glyphicon-bell" aria-hidden="true"></span>
        <span class="count label"><?= $count_new ?></span>
    </a>

    <div class="notify_content popover bottom">
        <div class="arrow"></div>
        <?php /*<a href="<?=Yii::app()->createUrl('notify/notifySetting')?>" class="pull-right noty-setting"><?=Yii::t('notify','settings')?></a>*/ ?>
        <h3 class="popover-title"><?= Yii::t('notify', 'notifications') ?></h3>

        <div class="clearfix"></div>
        <div class="popover-content">
            <?php $this->render('_content', array(
                'assetsPath' => $assetsPath,
                'model' => $model
            )); ?>
        </div>
        <?php if ($model && !Yii::app()->user->isGuest): ?>
            <a href="<?= Yii::app()->createUrl('notify/notify/all') ?>"
               class="show_all btn btn-link"><?= Yii::t('notify', 'show all') ?></a>
        <?php endif; ?>
    </div>
</div>