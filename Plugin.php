<?php
if(!defined('__TYPECHO_ROOT_DIR__'))
    exit;

/**
 * Typecho 附件上传Github仓库插件 <br> 基于AyagawaSeirin的UploadGithubForTypecho <br> 原版现由于GitHub Token验证方式更新无法使用
 * @package GitHubFile
 * @author Jcean
 * @version 1.0.0
 * @link https://www.jcean.com
 */
class GitHubFile_Plugin implements Typecho_Plugin_Interface
{
    //上传文件目录
    const UPLOAD_DIR = '/usr/uploads';

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * @access public
     * @return string
     */
    public static function activate(): string {
        Typecho_Plugin::factory('Widget_Upload')->uploadHandle = [
            'GitHubFile_Plugin',
            'uploadHandle'
        ];
        Typecho_Plugin::factory('Widget_Upload')->modifyHandle = [
            'GitHubFile_Plugin',
            'modifyHandle'
        ];
        Typecho_Plugin::factory('Widget_Upload')->deleteHandle = [
            'GitHubFile_Plugin',
            'deleteHandle'
        ];
        Typecho_Plugin::factory('Widget_Upload')->attachmentHandle = [
            'GitHubFile_Plugin',
            'attachmentHandle'
        ];
        Typecho_Plugin::factory('Widget_Upload')->attachmentDataHandle = [
            'GitHubFile_Plugin',
            'attachmentDataHandle'
        ];
        return _t('插件已激活，请前往设置');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * @static
     * @access public
     * @return void
     */
    public static function deactivate() {
    }

    /**
     * 获取插件配置面板
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form) {
        echo <<<HTML
            <style>
                p.notice {
                    line-height: 1.75;
                    padding: .5rem .5rem .5rem .75rem;
                    border-left: solid 4px #0099FF;
                    background: rgba(0, 0, 25, .025);
                }
                .notice {
                    background: #FFF6BF;
                    color: #0099FF;
                }
            </style>
            <p id="check-update" class="notice">正在检查插件更新...</p>
        HTML;

        echo <<<JAVASCRIPT
            <script src="//cdn.jsdelivr.net/gh/jquery/jquery/dist/jquery.min.js"></script>
            <script>
                window.onload = function() {
                    let notice = '正在检查更新...'
                    $.ajax({
                        url: 'https://api.github.com/repos/Jcean/UploadGithub-Typecho/releases',
                        type: 'GET',
                        success: res => {
                            if(res && res.length > 0) {
                                let version = '1.0.0'
                                let netVersion = res[0]['tag_name']
                                if(netVersion == null) notice = '检查更新失败，请手动访问插件项目地址获取更新。'
                                else if(version === netVersion) notice = '您当前的插件是最新版本：v' + netVersion
                                else notice = '插件需要更新，当前版本：v' + netVersion + '。<a target="_blank" href="https://github.com/Jcean/GitHubFile-Typecho/releases">点击这里</a>获取新版本'
                            } else notice = '检查更新失败，请手动访问<a target="_blank" href="https://github.com/Jcean/GitHubFile-Typecho/releases">项目地址</a>获取更新。'
                        },
                        error: () => {
                            notice = '检查更新失败，请手动访问<a target="_blank" href="https://github.com/Jcean/GitHubFile-Typecho/releases">项目地址</a>获取更新。'
                        },
                        complete: () => {
                            notice = '您当前的插件是最新版本：v1.0.0'
                            $('#check-update').html(notice)
                        }
                    })
                }
            </script>
        JAVASCRIPT;

        $tip1 = new J_SubTitle_Plugin('JSubTitle', null, null, _t('插件使用说明：'));
        $tip1->description(_t("<ol>
                <li>本插件用于将文章附件(如图片)上传至您的(公开的)Github的仓库中，并使用jsDelivr访问仓库文件达到优化文件访问速度的目的。了解jsDelivr应用于博客中的优势，您可以<a href='https://www.jcean.com/archives/68.html' target='_blank'>点击这里</a>。</li>
                <li>项目地址：<a href='https://github.com/Jcean/GitHubFile-Typecho' target='_blank'>https://github.com/Jcean/GitHubFile-Typecho</a></li>
                <li>插件使用说明与教程：<a href='https://www.jcean.com/archives/80.html' target='_blank'>https://www.jcean.com/archives/80.html</a></li>
                <li>插件不会验证配置的正确性，请自行确认配置信息正确，否则不能正常使用。</li>
                <li>插件会替换所有之前上传的文件的链接，若启用插件前存在已上传的文件，请自行将其上传至仓库相同目录中以保证正常显示；同时，禁用插件也会导致链接恢复。上传的文件保存在本地的问题请看下面相关配置项。</li>
                <li>注意：由于CDN缓存问题，修改文件后访问链接可能仍然是旧文件，所以建议删掉旧文件再上传新文件，不建议使用修改文件功能。jsDelivr刷新缓存功能暂未推出，推出后本插件会及时更新。</li>
                <li>Github API限制每个IP每小时只能请求60次接口，请控制您操作图片(上传修改删除)的频率。</li>
            </ol>"));

        $user = new Typecho_Widget_Helper_Form_Element_Text('user', null, '', _t('Github用户名'), null);

        $repo = new Typecho_Widget_Helper_Form_Element_Text('repo', null, '', _t('Github仓库名'), null);

        $token = new Typecho_Widget_Helper_Form_Element_Text('token', null, '', _t('Github账号token'), _t('不知道如何获取账号token请<a href="https://www.bilibili.com/read/cv4627037" target="_blank">点击这里</a>'));

        $directory = new Typecho_Widget_Helper_Form_Element_Text('directory', null, '/usr/uploads', _t('Github仓库内的上传目录'), _t('比如/usr/uploads，最后一位不需要斜杠'));

        $type = new Typecho_Widget_Helper_Form_Element_Select('type', [
            'latest' => '访问最新版本',
            'direct' => '直接访问'
        ], 'latest', _t('文件链接访问方式：'), _t('建议选择"访问最新版本"。若修改图片，直接访问方式不方便更新缓存。'));

        $tip2 = new J_SubTitle_Plugin('JSubTitle', null, null, _t('是否保存在本地：'));
        $tip2->description(_t('由于Linux权限问题，可能会由于无法创建目录导致文件保存到本地失败而报错异常，请给予本地上传目录777权限。<br>
        您也可以选择不保存到本地，但可能导致您的主题或其他插件的某些功能异常。<br>您也可以在每一月手动创建当月的目录，避免出现目录创建失败问题（推荐）。'));

        $local = new Typecho_Widget_Helper_Form_Element_Select('local', [
            '不保存到本地',
            '保存到本地'
        ], 0, _t(''));

        $tip3 = new J_SubTitle_Plugin('JSubTitle', null, null, _t('提交信息：'));
        $tip3->description(_t('以下两个参数为选填，留空则为仓库所有者信息。若填写则必须两个都填写。如果您不知道该如何填写，默认即可，不需要修改。'));

        $name = new Typecho_Widget_Helper_Form_Element_Text('name', null, 'GitHubFile', _t('提交文件者名称'), _t('提交Commit的提交者名称，留空则为仓库所属者。'));

        $email = new Typecho_Widget_Helper_Form_Element_Text('email', null, 'GitHubFile@Typecho.com', _t('提交文件者邮箱'), _t('提交Commit的提交者邮箱，留空则为仓库所属者。'));

        $form->addItem($tip1);
        $form->addInput($user->addRule('required', _t('请输入Github用户名')));
        $form->addInput($repo->addRule('required', _t('请输入Github仓库名')));
        $form->addInput($token->addRule('required', _t('请输入Github账号token')));
        $form->addInput($directory->addRule('required', _t('请输入Github上传目录')));
        $form->addInput($type);
        $form->addItem($tip2);
        $form->addInput($local);
        $form->addItem($tip3);
        $form->addInput($name);
        $form->addInput($email);
    }

    /**
     * 个人用户的配置面板
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form) {
    }

    /**
     * 上传文件
     * @param array $file
     * @return array|false
     * @throws Typecho_Exception
     */
    public static function uploadHandle(array $file) {
        if(empty($file['name']))
            return false;

        $ext = self::getFileExt($file['name']);

        // 判定是否是允许的文件类型
        if(!Widget_Upload::checkFileType($ext) || Typecho_Common::isAppEngine())
            return false;

        // 获取设置参数
        $options = Typecho_Widget::widget('Widget_Options')->plugin('GitHubFile');

        // 获取文件名
        $date = new Typecho_Date($options->gmtTime);
        $fileDirRelatively = self::getUploadDir(true) . '/' . $date->year . '/' . $date->month;
        $fileDir = self::getUploadDir(false) . '/' . $date->year . '/' . $date->month;
        $fileName = sprintf('%u', crc32(uniqid())) . '.' . $ext;
        $pathRelatively = $fileDirRelatively . '/' . $fileName;
        $path = $fileDir . '/' . $fileName;

        // 获得上传文件
        $uploadFile = self::getUploadFile($file);
        // 如果没有临时文件，则退出
        if(!isset($uploadFile))
            return false;
        $fileContent = file_get_contents($uploadFile);

        // 上传到Github
        $data = [
            'message' => 'Upload file ' . $fileName,
            'content' => base64_encode($fileContent),
        ];

        if($options->name != null && $options->email != null)
            $data['committer'] = [
                'name'  => $options->name,
                'email' => $options->email
            ];

        $header = [
            'Content-Type:application/json',
            'User-Agent:' . $options->repo,
            'Authorization: token ' . $options->token
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/repos/' . $options->user . "/" . $options->repo . '/contents' . $pathRelatively);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if($httpCode != 201) {
            $output = json_decode($output, true);
            self::writeErrorLog($pathRelatively, '[Github][upload][' . $httpCode . ']' . $output['message']);
        }

        // 写入本地文件
        if($options->local == 1) {
            if(!is_dir($fileDir)) {
                if(self::makeUploadDir($fileDir))
                    file_put_contents($path, $fileContent);
                else self::writeErrorLog($pathRelatively, '[local]Directory creation failed'); //写入失败记录日志
            }
            else file_put_contents($path, $fileContent);
        }

        // 返回相对存储路径
        return [
            'name' => $file['name'],
            'path' => $pathRelatively,
            'size' => $file['size'],
            'type' => $ext,
            'mime' => @Typecho_Common::mimeContentType($path)
        ];
    }

    /**
     * 修改文件
     * @param array $content
     * @param array $file
     * @return array|false
     * @throws Typecho_Exception
     */
    public static function modifyHandle(array $content, array $file) {
        if(empty($file['name']))
            return false;

        $ext = self::getFileExt($file['name']);

        // 判定是否是允许的文件类型
        if($content['attachment']->type != $ext || Typecho_Common::isAppEngine())
            return false;

        // 获取设置参数
        $options = Typecho_Widget::widget('Widget_Options')->plugin('GitHubFile');

        // 获取文件路径
        $path = $content['attachment']->path;

        // 获得上传文件
        $uploadFile = self::getUploadFile($file);

        // 如果没有临时文件，则退出
        if(!isset($uploadFile))
            return false;
        $fileContent = file_get_contents($uploadFile);

        // 判断仓库内相对路径
        $fileName = __TYPECHO_ROOT_DIR__ . $path; // 本地文件绝对路径
        $githubPath = $options->directory . str_replace(self::getUploadDir(), '', $content['attachment']->path);

        // 获取文件sha
        $header = [
            'Content-Type:application/json',
            'User-Agent:' . $options->repo,
            'Authorization: token ' . $options->token
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/repos/' . $options->user . '/' . $options->repo . '/contents' . $githubPath);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = json_decode(curl_exec($ch), true);
        curl_close($ch);
        $sha = $output['sha'];

        // 更新Github仓库文件
        $data = [
            'message' => 'Modify file ' . str_replace(self::getUploadDir(), '', $content['attachment']->path),
            'content' => base64_encode($fileContent),
            'sha'     => $sha,
        ];

        if($options->name != null && $options->email != null)
            $data['committer'] = [
                'name'  => $options->name,
                'email' => $options->email
            ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/repos/' . $options->user . '/' . $options->repo . '/contents' . $path);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if($httpCode != 200) {
            $output = json_decode($output, true);
            self::writeErrorLog($githubPath, '[Github][modify][' . $httpCode . ']' . $output['message']);
        }

        // 开始处理本地的文件
        if($options->local == 1) {
            if(file_exists($fileName))
                unlink($fileName);
            file_put_contents($fileName, $fileContent);
        }

        if(!isset($file['size']))
            $file['size'] = filesize($path);

        // 返回相对存储路径
        return [
            'name' => $content['attachment']->name,
            'path' => $content['attachment']->path,
            'size' => $file['size'],
            'type' => $content['attachment']->type,
            'mime' => $content['attachment']->mime
        ];
    }

    public static function deleteHandle(array $content) {
        //获取设置参数
        $options = Typecho_Widget::widget('Widget_Options')->plugin('GitHubFile');

        //判断仓库内相对路径
        $fileName = __TYPECHO_ROOT_DIR__ . $content['attachment']->path;
        $githubPath = $options->directory . str_replace(self::getUploadDir(), "", $content['attachment']->path);

        //获取文件sha
        $header = [
            'Content-Type:application/json',
            'User-Agent:' . $options->repo,
            'Authorization: token ' . $options->token
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/repos/' . $options->user . '/' . $options->repo . '/contents' . $githubPath);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = json_decode(curl_exec($ch), true);
        curl_close($ch);
        $sha = $output['sha'];

        // 删除Github仓库内文件
        $data = [
            'message' => 'Delete file',
            'sha'     => $sha,
        ];

        if($options->name != null && $options->email != null)
            $data['committer'] = [
                'name'  => $options->name,
                'email' => $options->email
            ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/repos/' . $options->user . "/" . $options->repo . "/contents" . $githubPath);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $output = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if($http_code != 200) {
            $output = json_decode($output, true);
            self::writeErrorLog($githubPath, '[Github][delete][' . $http_code . ']' . json_encode($output));
            return false;
        }

        //删除本地文件
        if($options->local == 1 && file_exists($fileName))
            unlink($fileName);

        return true;
    }

    /**
     * 获取实际文件绝对访问路径
     * @param array $content
     * @return string
     * @throws Typecho_Exception
     */
    public static function attachmentHandle(array $content): string {
        $options = Typecho_Widget::widget('Widget_Options')->plugin('GitHubFile');
        $latest = '';
        if($options->type == 'latest') {
            $latest = '@latest';
        }
        return Typecho_Common::url($content['attachment']->path, 'https://cdn.jsdelivr.net/gh/' . $options->user . '/' . $options->repo . $latest);
    }

    /**
     * 获取实际文件数据
     * @param array $content
     * @return false|string
     * @throws Typecho_Exception
     */
    public static function attachmentDataHandle(array $content) {
        $options = Typecho_Widget::widget('Widget_Options')->plugin('GitHubFile');
        $filePath = 'https://cdn.jsdelivr.net/gh/' . $options->user . '/' . $options->repo . '@latest' . $content['attachment']->path;
        return file_get_contents($filePath);
    }

    /**
     * 记录错误日志
     * @param string $path
     * @param string $content
     */
    private static function writeErrorLog(string $path, string $content): void {
        $date = date('[Y/m/d H:i:s]', time());
        $text = $date . ' ' . $path . ' ' . $content . '\n';
        $logFile = dirname(__FILE__) . "/error.log";
        $file = fopen($logFile, file_exists($logFile) ? 'ab+' : 'w');
        fwrite($file, $text);
    }

    /**
     * 获取文件扩展名
     * @param $name
     * @return string
     */
    private static function getFileExt($name): string {
        $name = str_replace([
            '"',
            '<',
            '>'
        ], '', $name);
        $name = str_replace('\\', '/', $name);
        $name = false === strpos($name, '/') ? ('a' . $name) : str_replace('/', '/a', $name);
        $info = pathinfo($name);
        return isset($info['extension']) ? strtolower($info['extension']) : '';
    }

    /**
     * 获取文件上传目录
     * @param bool $relatively
     * @return string
     */
    private static function getUploadDir(bool $relatively = true): string {
        $dir = defined('__TYPECHO_UPLOAD_DIR__') ? __TYPECHO_UPLOAD_DIR__ : self::UPLOAD_DIR;
        if($relatively)
            return $dir;
        return Typecho_Common::url($dir, defined('__TYPECHO_UPLOAD_ROOT_DIR__') ? __TYPECHO_UPLOAD_ROOT_DIR__ : __TYPECHO_ROOT_DIR__);
    }

    /**
     * 获取上传文件
     * @param array $file
     * @return string
     */
    private static function getUploadFile(array $file): string {
        return $file['tmp_name'] ?? ($file['bytes'] ?? ($file['bits'] ?? ''));
    }

    /**
     * 创建上传路径
     * @param $path
     * @return bool
     */
    private static function makeUploadDir($path): bool {
        $path = preg_replace('/\\\+/', '/', $path);
        $current = rtrim($path, '/');
        $last = $current;

        while(!is_dir($current) && strpos($path, '/') !== false) {
            $last = $current;
            $current = dirname($current);
        }

        if($last == $current)
            return true;

        if(!@mkdir($last))
            return false;

        $stat = @stat($last);
        $perms = $stat['mode'] & 0007777;
        @chmod($last, $perms);

        return self::makeUploadDir($path);
    }
}

class J_Title_Plugin extends Typecho_Widget_Helper_Form_Element
{
    public function value($value) {
        return parent::value($value);
    }

    public function label($value) {
        if(empty($this->label)) {
            $this->label = new Typecho_Widget_Helper_Layout('label', [
                'class' => 'typecho-label',
                'style' => 'font-size: 2em;border-bottom: 1px #ddd solid;'
            ]);
            $this->container($this->label);
        }

        $this->label->html($value);
        return $this;
    }

    public function input($name = null, array $options = null) {
        $input = new Typecho_Widget_Helper_Layout('p', []);
        $this->container($input);
        $this->inputs[] = $input;
        return $input;
    }

    public function message($message) {
        if(empty($this->message)) {
            $this->message = new Typecho_Widget_Helper_Layout('p', ['class' => 'message notice']);
            $this->container($this->message);
        }

        $this->message->html($message);
        return $this;
    }

    protected function _value($value) {
    }
}

class J_SubTitle_Plugin extends J_Title_Plugin
{
    public function label($value) {
        if(empty($this->label)) {
            $this->label = new Typecho_Widget_Helper_Layout('label', ['class' => 'typecho-label']);
            $this->container($this->label);
        }

        $this->label->html($value);
        return $this;
    }
}