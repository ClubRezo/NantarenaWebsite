<?php

namespace Nantarena\ForumBundle\Twig;

class BbcodeExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            'bbcode' => new \Twig_Filter_Method($this, 'bbcode', array('is_safe' => array('html'))),
        );
    }

    public function bbcode($str)
    {
        $expressions = array(
            '~\[b\](.*?)\[/b\]~s',
            '~\[i\](.*?)\[/i\]~s',
            '~\[u\](.*?)\[/u\]~s',
            '~\[quote\](.*?)\[/quote\]~s',
            '~\[size=(.*?)\](.*?)\[/size\]~s',
            '~\[color=(.*?)\](.*?)\[/color\]~s',
            '~\[url=((?:ftp|https?)://.*?)\](.*?)\[/url\]~s',
            '~\[img\](https?://.*?\.(?:jpg|jpeg|gif|png|bmp))\[/img\]~s',
            '~\[list=1\](.*?)\[/list\]~s',
            '~\[list\](.*?)\[/list\]~s',
            '~\[\*\](.*?)\n~s',
        );

        $replace = array(
            '<b>$1</b>',
            '<i>$1</i>',
            '<span style="text-decoration:underline;">$1</span>',
            '<div class="forum-quote">$1</div>',
            '<span style="font-size:$1%;">$2</span>',
            '<span style="color:$1;">$2</span>',
            '<a href="$1" target="_blank">$2</a>',
            '<img src="$1" alt="User image invalid" style="max-width: 600px;"/>',
            '<ul>$1</ul>',
            '<ol>$1</ul>',
            '<li>$1</li>',
        );

        return preg_replace($expressions, $replace, $str);
    }

    public function getName()
    {
        return 'bbcode_extension';
    }
}