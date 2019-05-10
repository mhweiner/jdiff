<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mweiner
 * Date: 9/19/13
 * Time: 12:51 PM
 * To change this template use File | Settings | File Templates.
 */
class ResultsView
{
    public function printContent($data){
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Testing Results</title>
            <link rel="stylesheet" href="/assets/css/results.css" />
        </head>
        <body>

            <?
            foreach($data as $v){
                ?>
                <ul>
                    <li>
                        <h2><?=$v['url']?></h2>
                        <div><img src="<?=$v['img1']?>"></div>
                        <div><img src="<?=$v['img2']?>"></div>
                    </li>
                </ul>
                <?
            }
            ?>

        </body>
        </html>
        <?
    }
}
