<?php
/**
 * @var FrontController $this
 * @var Notify[] $notifications
 * @var CPagination $pages
 */

?>

<div class="container">
    <h1><?= Yii::t('notify', 'All notifications') ?></h1>
    <? if ($notifications) { ?>
        <div id="all_notifications" class="notify_content">
            <?php foreach ($notifications as $notify) {
                $this->renderPartial('_notify', [
                    'notify' => $notify
                ]);
            } ?>
        </div>
        <!-- Enable infinite scroll -->
        <?php
            $this->widget('ext.yiinfinite-scroll.YiinfiniteScroller', array(
                'contentSelector' => '#all_notifications',
                'itemSelector' => '#all_notifications div.notify',
                'pages' => $pages,
                'img' => Yii::app()->theme->baseUrl . '/img/ajax-loader-infinity.gif',
                'msgText' => '<i>Loading more notifications...</i>',
                'finishedMsg' => '<i>Congratulations, all notices ended.</i>',
            ));
        ?>

    <? } else { ?>
        <div class="no_notify"><?= Yii::t('notify', 'no notifications') ?></div>
    <?}?>
</div>