### 多平台登陆###

相同用户，简单实现三个站点的单点登录，在一个站点登录，其他站点自动登录，一个站点退出，其他站点同时退出

说明：功能的实现基于cookie，如果浏览器关闭了cookie的功能，将无法实现多点登陆。

假设有三个站点(可自由扩充)

* siteA 域名为sitea.com绑定了2223端口
* siteB 域名为siteb.com绑定了2222端口
* siteC 域名为sitec.com绑定了2221端口

思路：

1. memcache站点SESSION的共享
2. SESSION ID传递所有站点能共享一个登录凭证

#### 使用php自带Soap扩展，简单实现一个接口服务；所以需要开启扩展####

