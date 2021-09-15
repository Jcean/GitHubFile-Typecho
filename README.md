# GitHubFile-Typecho

附件上传GitHub仓库  GitHubFile For Typecho

## 界面

![GitHubFile](https://cdn.jsdelivr.net/gh/Jcean/BlogStatic@latest/usr/uploads/2021/09/2176525845.jpg)

## 介绍

1. 用于将文章附件上传至 Github 的仓库中
2. 添加图片进入文章时会替换链接为 js­De­livr 的地址
3. 本插件利用此服务来加速文章附件（图片等）访问速度
4. 关于 js­De­livr 运用于博客的优势本文不再赘述，具体请访问[这里](https://www.jcean.com/archives/68.html)

## 声明

本作品仅供个人学习研究使用，请勿将其用作商业用途。  
基于AyagawaSeirin的UploadGithubForTypecho，原插件由于GitHub Token验证方法更新已无法使用。

## 安装

1. 在项目页面右上角点击 Download ZIP 下载压缩包
2. 上传到 /usr/plugins 目录
3. **修改文件夹名为 GitHubFile**
4. 后台启用插件

## 使用

1. 在GitHub建立一个公开仓库
2. 配置仓库及基础信息
   
Github 用户名：必填，您的 Github 用户名。
Github 仓库名：必填，您用于储存附件文件的仓库名称。  
Github 账号 To­ken：必填，您的 Github 账号的 Token，不知道如何获取账号Token 请点击[这里](https://www.bilibili.com/read/cv4627037)。  
Github 仓库内的上传目录：必填，附件上传到的仓库内目录位置。如果您不知道如何填写，建议保持默认内容。  
文件链接访问方式：建议选择 "访问最新版本"。若修改图片，直接访问方式不方便更新缓存。  
是否保存在本地：是否将文件保存到本地。 
   
> 以下两个参数为选填，留空则为仓库所有者信息。    
> 若填写则必须两个都填写。  
> 如果您不知道该如何填写，默认即可，不需要修改。  
> 不建议留空，这样可以区分哪些文件是插件提交的。建议您保持默认内容。

提交文件者名称：选填，提交 Com­mit 的提交者名称，留空则为仓库所属者。  
提交文件者邮箱：选填，提交 Com­mit 的提交者邮箱，留空则为仓库所属者。
   

## FAQ

> Q：是否会验证配置的正确性  
> A：插件不会验证配置的正确性，请自行确认配置信息正确，否则不能正常使用。


> Q：本地文件无法保存  
> A：由于 Linux 权限问题，可能会由于无法创建目录导致文件保存到本地失败而报错异常，请给予本地上传目录 777 权限。  
> 您也可以选择不保存到本地，但可能导致您的主题或其他插件的某些功能异常。  
> 您也可以在每一月手动创建当月的目录，避免出现目录创建失败问题（推荐）。


> Q：修改文件不更新  
> A：由于 CDN 缓存问题，修改文件后访问链接可能仍然是旧文件，所以建议删掉旧文件再上传新文件，不建议使用修改文件功能。js­De­livr 刷新缓存功能暂未推出，推出后本插件会及时更新。


> Q：插件更新  
> A：在插件设置页面会自动检查更新，若检查失败请手动前往项目地址检查更新。


> Q：Token限制
> A：GitHub API 限制每个 IP 每小时只能请求 60 次接口，请控制您操作图片 (上传修改删除) 的频率。

更多问题可以通过 issue 页面提交，或者通过留言、邮件向我反馈

## LICENSE

GitHubFile-Typecho is under the MIT license.

[hide][Gitee项目地址](https://gitee.com/Jcean/GitHubFile-Typecho)
[GitHub项目地址](https://github.com/Jcean/GitHubFile-Typecho)
[/hide]