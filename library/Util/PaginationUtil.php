<?php
/**
 * Created by PhpStorm.
 * User: lanse
 * Date: 17-2-22
 * Time: 下午7:41
 */

namespace Root\Library\Util;


class PaginationUtil
{
    /**
     * @param $options
     * @return array|string
     */
    public static function getPages($options)
    {
        if (!isset($options['total_item']) || !is_numeric($options['total_item']) || $options['total_item'] <= 0) {
            return '';
        }
        $params = $_GET;
        $pageQueryName = 'page';

        if (isset($options['page_query_name'])) {
            $pageQueryName = $options['page_query_name'];
        }

        $perPage = 10;
        if (isset($options['per_page'])) {
            $perPage = $options['per_page'];
        }

        $currentPage = isset($params[$pageQueryName]) ? $params[$pageQueryName] : 1;
        $pagination = new \Kilte\Pagination\Pagination($options['total_item'], $currentPage, $perPage, 2);
        $pages = $pagination->build();
        if (count($pages) <= 1) {
            return [];
        }

        $baseUrl = self::getBaseUrl();
        $pagerInfo = [];
        foreach ($pages as $page => $tips) {
            $show = $page;
            if ($tips == 'less') {
                $show = '«';
            } elseif ($tips == 'more') {
                $show = '»';
            }

            $params[$pageQueryName] = $page;

            $pagerInfo[] = [
                'page' => $page,
                'tips' => $tips,
                'show' => $show,
                'url' => $baseUrl . '?' . http_build_query($params)
            ];
        }
        return $pagerInfo;

    }

    /**
     * @return string
     */
    public static function getBaseUrl()
    {
        return UrlUtil::getBaseUrl();
    }

}