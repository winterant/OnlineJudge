# 配置邮箱

有些场景，例如重置密码，需要向用户的邮箱发送验证邮件，这就需要网站配置了可发送邮件的邮箱。下面是配置方法。

## 📮 QQ邮箱/Foxmail邮箱

### 一、获取邮箱令牌
1. 前往[QQ邮箱网页版](https://mail.qq.com/)登陆自己邮箱；
2. 点击【设置】，切换到【账户】；
3. 翻到【POP3/IMAP/SMTP/Exchange/CardDAV/CalDAV服务】；
4. 开启【POP3/SMTP服务】和【IMAP/SMTP服务】；
5. 点击【生成授权码】即可获取到令牌（即登陆密码）。该授权码只会显示一次，一定要记下来。

### 二、填写配置文件
编辑宿主机OnlineJudge项目文件夹下的`lduoj.conf`，找到下列配置并填写：
```shell
MAIL_MAILER=smtp                    # 服务类型
MAIL_HOST=smtp.qq.com               # QQ邮箱的smtp网址
MAIL_PORT=465                       # QQ邮箱的端口
MAIL_USERNAME=xxxxx@qq.com          # (必填)你的QQ邮箱或Foxmail邮箱
MAIL_PASSWORD=                      # (必填)在QQ邮箱网页版中获取到的令牌
MAIL_ENCRYPTION=ssl                 # 加密方式，默认即可
MAIL_FROM_ADDRESS=${MAIL_USERNAME}  # 同 MAIL_USERNAME
MAIL_FROM_NAME="LDU Online Judge"   # (必填)向用户发送邮件时显示的发件人名字
```

修改配置后，重启服务生效：
```bash
docker compose up -d
```

## 📬 163邮箱/126邮箱

时间有限，尚未测试，来日补上。

## 📫 其它可提供smtp的邮箱

仿照QQ邮箱的配置，提供配置项参数值即可。
