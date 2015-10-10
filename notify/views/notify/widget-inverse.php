<?php
/**
 * @var int $timeUpdateCenter
 * @var string $notifyUrlUpdate
 * @var int $count_new
 *
 * @var $clientScript CClientScript
 */
$clientScript = Yii::app()->clientScript;
$clientScript->registerScript('notify_config', '
    $.fn.notifyCenter({
        "notifyTimeUpdate":' . $timeUpdateCenter . ',
        "notifyUrlUpdate":"' . $notifyUrlUpdate . '",
        "notifyUrlRead":"' . Yii::app()->createUrl('/notify/notify/readNotify') . '"
    });
', CClientScript::POS_END);

?>
<div class="notification_center">
    <a class="notify_button btn <?= ($count_new ? 'active' : '') ?>">
        <span class="ret-icon ret-icon-bell ret-icon--inverse" aria-hidden="true"></span>
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
        <?php if ($model): ?>
            <a href="<?= Yii::app()->createUrl('notify/notify/all') ?>"
               class="show_all btn btn-link"><?= Yii::t('notify', 'show all') ?></a>
        <?php endif; ?>
    </div>
</div>