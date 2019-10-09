<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <style type="text/css">
            * { font-family: arial; }
            body { font-size: 12pt; line-height: 13.2pt; }
            h3 { font-size: 12pt; line-height: 14.4pt; font-weight: normal; text-transform: uppercase; }
        </style>
    </head>

    <body>

        <table cellspacing="0" cellpadding="0">
            <tr>
                <td width="680" valign="top" style="width:511pt; padding:0cm;">

                    <div>
                        <table cellspacing="0" cellpadding="0">
                            <tr>
                                <td align="left" width="150"><img src="<?= __DIR__ ?>/../../resources/images/logo.png" width="200"></td>
                            </tr>
                        </table>
                    </div>

                    <div style="margin-left:75px;">

                        <h3>Здравствуйте!</h3>

                        <p>Для Вашей учетной записи «<?= $notification->profileLogin ?>» на сайте был изменён пароль.</p>

                        <p>Если Вы этого не делали, обратитесь к Вашему персональному менеджеру <?= $notification->managerName ?> <?= $notification->managerEmail ?></p>

                    </div>
                </td>
            </tr>
        </table>

    </body>
</html>
