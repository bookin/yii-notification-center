### Install

Apply migrations

```
php yiic.php migrate --migrationPath=application.modules.notify.migrations
```

Connect component to the config:

```
'components' => array(
    'notify'=>array(
        'class'=>'application.modules.notify.components.NotifyComponent',
        'countSaveOld'=>false //if need save only a certain amount of notifications, set the number in this property if don't need delete old notification, please set false this property
        'timeUpdateCenter' = 15 * 1000, //how soon check for new notifications
        'numberDisplay' => 6, //ow many notifications will be displayed in a popup window
        'numberDisplayInAll' => 20, //how many notifications will be displayed in page where shows all notifications
        'defaultImage' => '{themeUrl}/img/urbantip-mini-mottotrue.png', //Url to default image, you can user special words:
                                                                        //  '{themeUrl}' == Yii::app()->theme->baseUrl
                                                                        //  '{basePath}' == Yii::app()->basePath
                                                                        //  '{baseUrl}'  == Yii::app()->baseUrl
        'forGuest'=>'Please sign up', //notification for guest
    ),
    ...
)
```

Connect module to the config:

```
'modules' => array(
    'notify',
    ...
)
```

Add models to import:

```
'import' => [
    'application.modules.notify.models.*',
]
```

Add widget to the page:

```
$this->widget('notify.components.WidgetNotifyCenter');
```

You can check the page URL , If a URL with parameters there's in the table with the notifications for the current user, the notification will change the status to read.
If it need, you  have to add `NotifyUrlHandler` to `preload` property in config

```
'preload'=>array('notifyUrl'),
'components' => array(
    'notifyUrl'=>array(
        'class'=>'application.modules.notify.components.NotifyUrlHandler',
    ),
    ...
)
```

### Uses

if you need send notification to user, you can use two methods

###### Method 1

`Yii::app()->notify->create()` - return Notify model, and you can use all the functions available for the model

```php
Yii::app()->notify->create()
    ->setUserId(2) // to whom you want to send a notification, or Notify::ALL_USERS (or 0) if you want send notification to all users
    ->setHeader('header')
    ->setDescription('description')
    ->setImage('/path/to/image.jpg')
    ->setUrl(['controller/action',['param'=>'value']]) // this parameter uses a CHtml::normalizeUrl to create url
    ->setDateShow(date('c')) // you can set date when notification will must send to user, default date('c') 
    ->setReadStatus(Notify::NOT_READ) // you can change status 'read' or 'not read'
    ->send(); // it's sending notification to bd, also you can use ->save();
```

`header, description, user_id` - it's a required parameters.

###### Method 2

```php
Yii::app()->notify->addNotify($options=[]);
Yii::app()->notify->addNotifyForUser($user_id, $options=[]);
Yii::app()->notify->addNotifyForAllUsers($options=[]);
```

`$options` it's a array with Notify model parameters

```php
$options = [
    'user_id'=>2, // to whom you want to send a notification, or Notify::ALL_USERS (or 0) if you want send notification to all users
    'header'=>'text',
    'description'=>'description text',
    'image'=>'/path/to/image.jpg',
    'url'=>['controller/action',['param'=>'value']], // this parameter uses a CHtml::normalizeUrl to create url
    'date_show'=>date('c'), // you can set date when notification will must send to user, default date('c') 
    'read'=>Notify::NOT_READ, // you can change status 'read' (Notify::READ) or 'not read' (Notify::NOT_READ)
];
```

`header, description, user_id` - it's a required parameters.


### Events

Model `Notify` have a events `onAddNotify`, `onUpdateNotify`, `onDeleteNotify`




[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/bookin/yii-notification-center/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

