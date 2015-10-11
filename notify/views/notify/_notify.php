<?php
/**
 * @var Notify $notify
 */
$blockParams=[
    'class'=>'clearfix notify'.($notify->isRead?' read':''),
    'data-id'=>$notify->id,
];

if($notify->url)
{
    $blockParams['onClick']="document.location.href = '".$notify->url."'";
}
?>

<div <?=CHtml::renderAttributes($blockParams);?>>
    <div class="left-block">
        <?php if($notify->img):?>
            <div class="image">
                <?= CHtml::image($notify->getImage());?>
                <img src="<?php echo $link; ?>">
            </div>
        <?php endif;?>
    </div>
    <div class="right-block">
        <div class="notify_head"> 
            <?=$notify->header?>
        </div>
        <div class="notify_description notificationSection">
            <?=$notify->description?>
        </div>
        <div class="date">
            <?=Notify::getFormatDate(isset($notify->status)&&$notify->status->date_showed?$notify->status->date_showed:$notify->date);?>
        </div>
    </div>
</div>