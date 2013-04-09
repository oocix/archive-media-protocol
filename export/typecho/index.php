<?php

/**
 * Typecho导出为存档的工具
 * Author : moyo <http://moyo.uuland.org/>
 * 注意：运行后会在脚本目录下生成存档文件
 */

// 数据库信息
$mysql = array(
	'server' => 'localhost',
	'username' => 'root',
	'password' => 'moyo',
	'database' => 'typecho',
	'prefix' => 'typecho_'
);
// 导出位置
$export = '.';

// programs start

$db = mysql_connect($mysql['server'], $mysql['username'], $mysql['password']);
$db || exit('db.connect.error');
mysql_select_db($mysql['database']) || exit('db.database.error');
mysql_query('SET NAMES "utf8"') || exit('db.charset.error');

// get all posts

$result = mysql_query('select * from '.$mysql['prefix'].'contents where type="post" and status="publish"');

while (false != $post = mysql_fetch_array($result))
{
	$meta = get_metas($post['cid']);
	$io = "";
	io_append($io, '# URI');
	io_append($io, '* slug = '.$post['slug']);
	io_append($io, '');
	io_append($io, '# META');
	io_append($io, '* topic = '.$meta['topic']);
	io_append($io, '* tags = '.implode(',', $meta['tags']));
	io_append($io, '* time = '.date('Y-m-d H:i:s', $post['created']));
	io_append($io, '');
	io_append($io, '# TITLE');
	io_append($io, $post['title']);
	io_append($io, '');
	io_append($io, '# CONTENT');
	io_append($io, str_replace("\r", "", $post['text']));
	io_save($io, $post['slug'].'.md');
	echo $post['title'].' ......... DONE<br/>';
}

function io_append(&$io, $line)
{
	$io = $io . $line . "\n";
}

function io_save($io, $path)
{
	global $export;
	file_put_contents($export.'/'.$path, $io);
}

function get_metas($cid)
{
	global $mysql;
	$return = array('topic' => '', 'tags' => array());
	// get mids
	$r_rls = mysql_query('select mid from '.$mysql['prefix'].'relationships where cid='.$cid);
	$mids = array();
	while (false != $midd = mysql_fetch_array($r_rls))
	{
		$mids[] = $midd['mid'];
	}
	// get metas
	$r_meta = mysql_query('select * from '.$mysql['prefix'].'metas where mid in ('.implode(',',$mids).')');
	while (false != $meta = mysql_fetch_array($r_meta))
	{
		if ($meta['type'] == 'category')
		{
			$return['topic'] = $meta['name'];
		}
		elseif ($meta['type'] == 'tag')
		{
			$return['tags'][] = $meta['name'];
		}
	}
	$return['tags'] || $return['tags'][] = $return['topic'];
	return $return;
}

?>
