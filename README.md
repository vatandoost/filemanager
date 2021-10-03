# Yii2 FileManager
this module is an easy way to save and manage your files in yii2  

features:
----
 * manage files in two main category : 
    * private
    * public
 * multiple handlers :
    * ftp or sftp
    * local
 * manger classes to manage any file type
 * selector widget for select files from filemanager
 * filetrait for models class to easily manage and save files
 * filemanager UI:
    * preview files
        * google docs viewer
        * lightbox image viewer
        * pdf viewer
        * jplayer video and audio preview
        * local office viewer by using unoconv
    * rename files 
    * delete files
    * search and sort files
 * upload files from another url
 * resize image
 * upload files using blueimp jquery file upload
 * FileHelper class to manage files in your app
 * get files query in your app to merge with other queries by selecting file addresses
 * use bootstrap4 theme
 * multi language


## Demo
[sample demo](https://github.com/vatandoost/filemanager_demo) 

**Screenshots** 

<a href="http://vatandoost.com/assets/filemanager/1.png">
    <img width="250px" src="http://vatandoost.com/assets/filemanager/1.png" alt="yii2 filemanager ui"/>
</a>
<a href="http://vatandoost.com/assets/filemanager/2.png">
    <img width="250px" src="http://vatandoost.com/assets/filemanager/2.png" alt="yii2 filemanager upload"/>
</a>
<a href="http://vatandoost.com/assets/filemanager/3.png">
    <img width="250px" src="http://vatandoost.com/assets/filemanager/3.png" alt="yii2 filemanager selector"/>
</a>
<a href="http://vatandoost.com/assets/filemanager/4.png">
    <img width="250px" src="http://vatandoost.com/assets/filemanager/4.png" alt="yii2 filemanager preview box"/>
</a>


## Installation

use composer
```
composer require vatandoost/filemanager
```

Apply migration
```
yii migrate --migrationPath=vendor/vatandoost/filemanager/migrations
```

Configuration

In your configuration file add filemanager module in modules
and also add filemanager module to your bootstrap components
```
'modules' => [
    'filemanager' => [
        'class' => 'vatandoost\filemanager\Module',
        'publicFiles' => '@frontend/web/uploads',
        'privateFiles' => '@frontend/uploads',
    ]
],
'bootstrap' => ['filemanager']
```

available configurations : 

```
'filemanager' => [
    'class' => 'vatandoost\filemanager\Module',
    'title' => 'Yii2 filemanager',
    'publicFiles' => '@frontend/web/uploads',
    'privateFiles' => '@frontend/uploads',
    'publicFilesUrl' => '/uploads',
    'host' => 'http://optional.com:20180', // this param is option, if not exist it uses current server info
    'params' => [
        'upload_files' => true,
        'url_upload' => true,
        'default_handler' => FileType::HANDLER_TYPE_LOCAL,

        'jplayer_exts' => array("mp4", "flv", "webmv", "webma", "webm", "m4a", "m4v", "ogv", "oga", "mp3", "midi", "mid", "ogg", "wav"),
        'cad_exts' => array('dwg', 'dxf', 'hpgl', 'plt', 'spl', 'step', 'stp', 'iges', 'igs', 'sat', 'cgm', 'svg'),

        // Preview with Google Documents
        'googledoc_enabled' => true,
        'googledoc_file_exts' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'odt', 'odp', 'ods'),


        'image_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'ico'],
        'file_extensions' => ['doc', 'docx', 'rtf', 'pdf', 'xls', 'xlsx', 'txt', 'csv', 'html', 'xhtml', 'psd', 'sql', 'log', 'fla', 'xml', 'ade', 'adp', 'mdb', 'accdb', 'ppt', 'pptx', 'odt', 'ots', 'ott', 'odb', 'odg', 'otp', 'otg', 'odf', 'ods', 'odp', 'css', 'ai', 'kmz', 'dwg', 'dxf', 'hpgl', 'plt', 'spl', 'step', 'stp', 'iges', 'igs', 'sat', 'cgm', 'tiff', ''], //Files
        'video_extensions' => ['mov', 'mpeg', 'm4v', 'mp4', 'avi', 'mpg', 'wma', "flv", "webm"],
        'audio_extensions' => ['mp3', 'mpga', 'm4a', 'ac3', 'aiff', 'mid', 'ogg', 'wav'],
        'misc_extensions' => ['zip', 'rar', 'gz', 'tar', 'iso', 'dmg'],

        //size of image boxes in filemanager ui
        'image_preview_size' => [
            'w' => 120,
            'h' => 100,
        ],
        'rename_files' => true,
        'delete_files' => true,
        // ftp handler options
        'ftp' => [
            'ftp_host' => "example.com", //put the FTP host
            'ftp_user' => "example",
            'ftp_pass' => "*****",
            'ftp_public_folder' => "/public_html/filemanager",
            'ftp_private_folder' => "/filemanager/uploads",
            'ftp_base_url' => "https://example.com/filemanager",
            'ftp_ssl' => false,
            'ftp_port' => 21,
        ],

        // convert office files to pdf format
        'convert_pdf_preview' => false,
        'unoconvBaseUrl' => 'localhost',
    ],
    'superAdminRole' => 'filemanager_admin',
]
```

How it works
---
there are two table to save files informations
* table file_type
* table files

**table file_type:**
it maintain information about files in a type . 
to use filemanager you have to create a file_type row at first.

if you don't define that when file saved in model it will be create. 

each file type have a manager class to manage their files ( Yii2 model classes )

fileType fields:
```php
    'file_type_id' => 'File Type ID', // integer autoincrement  
    'name' => 'Name', // unique name
    'title' => 'Title', // title to show in filemanager ui
    'is_public' => 'Is Public', // (boolean) show files are public or private 
    'mime_types' => 'Mime Types',
    'max_size' => 'Max Size', // maximum file size to upload
    'extensions' => 'Extensions', // like  "img,png,jpg"
    'files_path' => 'Files Path', // directory path to save files
    'manager_class' => 'manager class', // class to manage files if empty it will use \vatandoost\filemanager\libs\BaseManager  
    'handler_type' => 'type of handler class', // 1 for local handler 2 for Ftp Handler
    'has_public_thumb' => 'public thumbnail', // if files are private they can have public thumbnail for performance
    'has_force_relation_id' => 'force to has relation id to work with filemanager',
```
 
manager class always is a model that it can change some of behaviors of filemanager.

you can check bellow class to see available options and you can define any of them to your model

\vatandoost\filemanager\libs\BaseManager
 * filterQueryFiles($query, $fileType, $relation_id = null)
 * canView($file)
 * canDelete($file)
 * beforeFileDelete($file) 
 * afterFileDelete($file)
 * fileTypeConfig($fileType, $relationId = null)


filemanager ui is an iframe that show files to select or manage.
 you can use it as an standalone page or you can add it to your page

options to open filemanager : "filemanager/dialog/index"
  * unique_name : its combine of file_type_name and relation id that joined together with ','
  * selector: if you send selector=off , it disable selecting files only show them
  * callback: function name from parent window to call after select
  * field_id: input id to put file Ids into its value
  * multiple: (0/1) it shows can select multiple files or not ( default : 1)
for example : 

http://localhost/filemanager/frontend/web/filemanager/dialog/index?callback=filemanager_selector_callback&unique_name=frontend_models_testfile,36&multiple=1&field_id=testfile-files_str
  
usage
---

you can use this module without UI and selector,
 It can help you to upload files and access them easily
 
 you need to add fileTrait to your model and then define your attributes
 
 example model (manager class)
 
 ```php
class TestFile extends \yii\db\ActiveRecord
{

    use \vatandoost\filemanager\libs\FileTrait;

    public $file;
    
    public static function tableName()
    {
        return 'test_file';
    }

    public function rules()
    {
        return [
            [
                ['file'],
                'vatandoost\filemanager\libs\FileValidator', // you have to use this file validator or extend of that
                'file_type_name' => '', // if you did not define this it will complete based on class name  
                'relation_field' => 'id', // use this attribute to save in relation field in files table 
                //'target_field' => 'file_id', // file id will save in this attribute after save if multiple false
                'thumb' => [250, 250], // thumbnail size if files are image
                'multiple' => true // we can add multiple files for this field
                // you can define any other \yii\validators\FileValidator properties here
            ],
            // other attributes
            //[['file_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'required'],
            [['files_str'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            //'file_id' => 'File ID',
            'files_str' => 'File list',
        ];
    }
}
```

for upload files in yii2 you just need to run this function 
```php
$model->saveFiles()
```

example use of file upload in controller 
```php
public function actionCreate()
{
    $model = new TestFile();

    if ($model->load(Yii::$app->request->post()) && $model->save() && $model->saveFiles()) {
        return $this->redirect(['view', 'id' => $model->id]);
    }

    return $this->render('create', [
        'model' => $model,
    ]);
}
```

example use of file upload input:

```php
echo $form->field($model, 'file')->fileInput();
```

example use of file selector:
```php
echo $form->field($model, 'files_str')->widget(\vatandoost\filemanager\widgets\selector\Selector::class, [
    'fileTypeName' => 'frontend_models_testfile',
    'relationId' => $model->id,
    'multiple' => true
]);
```

there are some other functions to use : 
 * $model->getFiles($attribute, $onlyModels = false) // to get all files from model
 * $model->filesQuery($file_type_name = null) // to get ActiveQuery class to merge with other tables

there is a helper class too.
you can use that's static functions to work with your files across your app:

\vatandoost\filemanager\libs\FileHelper
 * FileHelper::getFileUrl($file_id, $thumb = false)
 * FileHelper::getFileInfo($file_id)
 * FileHelper::getFileInfoByModel(File $file)
 * FileHelper::deleteFile($file_id, $checkPermissions = true)
 * FileHelper::renameFile($file_id, $new_name)
 * FileHelper::deleteFileByModel(File $model, $checkPermissions = true)
 * FileHelper::getFileContent($id = null, $model = null)
 * FileHelper::getLocalFilePath($file, $fileType = null)

## Console Commands

after install module you can use below commands to manage file types:

```
#list of file types 
php yii filemanager/files/types 

# create new file type
php yii filemanager/files/create-type
```