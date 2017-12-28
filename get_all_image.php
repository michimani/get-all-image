<?php
date_default_timezone_set('Asia/Tokyo');

try
{
    if (!isset($argv[1]))
    {
        throw new Exception('The first argument is required for url.');
    }

    $url = $argv[1];
    if (!preg_match('/^(http|https):\/\//', $url))
    {
        throw new Exception('Invalid url.');
    }

    $html_source = file_get_contents($url);

    if ($html_source == null || $html_source == '')
    {
        throw new Exception('Failed to get html source from url.');
    }

    preg_match_all('/src="(.*?(\.jpg|\.jpeg|\.gif|\.png))"/i', $html_source, $matches);

    if (!isset($matches[1]) || count($matches[1]) === 0)
    {
        throw new Exception('No image file in url.');
    }

    $base_tmp = explode('/', $url);
    $base = sprintf('%s/%s/%s', $base_tmp[0], $base_tmp[1], $base_tmp[2]);
    echo $base."\n";

    $save_dir = sprintf('./save_img_%s', date('YmdHis'));
    if (!file_exists('./'.$save_dir))
    {
        mkdir('./'.$save_dir);
    }

    $save_cnt = 0;
    foreach($matches[1] as $img_url)
    {
        $fname_tmp = explode('/', $img_url);
        $fname_tmp = array_reverse($fname_tmp);
        $fpath = sprintf('%s/%s', $save_dir, $fname_tmp[0]);

        if (!preg_match('/^(http|https):\/\//', $img_url))
        {
            $img_url = sprintf('%s/%s', $base, $img_url);
        }
        $data = @file_get_contents( $img_url );
        if ( $data )
        {
            @file_put_contents( $fpath, $data );
        }

        if (!file_exists($fpath))
        {
            echo sprintf('ERROR: fialed to save img. {%s}%s', $img_url, "\n");
        }
        else
        {
            $save_cnt++;
        }
    }

    $message = sprintf('end. {all:%s, saves:%s}', count($matches[1]), $save_cnt);
}
catch (Exception $e)
{
    $message = $e->getMessage();
}
finally
{
    echo $message. "\n";
}
