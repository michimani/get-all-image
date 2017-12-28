<?php
date_default_timezone_set('Asia/Tokyo');

try
{
    if (!isset($argv[1]))
    {
        throw new Exception('The first argument is required for url.');
    }

    $url = $argv[1];
    if (!preg_match('/^https?:\/\//', $url))
    {
        throw new Exception('Invalid url.');
    }

    $header = @get_headers($url);
    if (!preg_match('/^HTTP\/.*\s+200\s/i', $header[0]))
    {
        throw new Exception('Target page does not found.');
    }

    $html_source = file_get_contents($url);

    if ($html_source == null || $html_source == '')
    {
        throw new Exception('Failed to get html source from url.');
    }

    preg_match_all('/src="(.*?(\.jpg|\.jpeg|\.gif|\.png))"/i', $html_source, $matches);

    echo strlen($html_source)."\n";

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

    $save_cnt = $duplicate_cnt = $error_cnt = 0;
    $saved_list = [];
    foreach($matches[1] as $k => $img_url)
    {
        $fname_tmp = explode('/', $img_url);
        $fname_tmp = array_reverse($fname_tmp);
        $fpath = sprintf('%s/%s_%s', $save_dir, $k, $fname_tmp[0]);

        if (!preg_match('/^https?:\/\//', $img_url))
        {
            $img_url = sprintf('%s/%s', $base, $img_url);
        }
        if (in_array($img_url, $saved_list))
        {
            $duplicate_cnt++;
            continue;
        }

        $data = @file_get_contents( $img_url );
        if ( $data )
        {
            @file_put_contents( $fpath, $data );
        }

        if (!file_exists($fpath))
        {
            $error_cnt++;
        }
        else
        {
            $save_cnt++;
            $saved_list[] = $img_url;
        }
    }

    $message = sprintf('end. {all:%s, saved:%s, duplicate:%s, error:%s}', count($matches[1]), $save_cnt, $duplicate_cnt, $error_cnt);
}
catch (Exception $e)
{
    $message = $e->getMessage();
}
finally
{
    echo $message. "\n";
}
