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
            '~\[url\](.*?)\[/url\]~s',
            '~\[img\](https?://.*?\.(?:jpg|jpeg|gif|png|bmp))\[/img\]~s',
            '~\[list=1\](.*?)\[/list\]~s',
            '~\[list\](.*?)\[/list\]~s',
            '~\[\*\](.*?)\n~s',
            '~((https?://?.*?[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))~s',
        );

        $replace = array(
            '<b>$1</b>',
            '<i>$1</i>',
            '<span style="text-decoration:underline;">$1</span>',
            '<div class="forum-quote">$1</div>',
            '<span style="font-size:$1%;">$2</span>',
            '<span style="color:$1;">$2</span>',
            '<a href="$1" target="_blank">$2</a>',
            '<a href="$1" target="_blank">$1</a>',
            '<img src="$1" alt="User image invalid" style="max-width: 600px;"/>',
            '<ol>$1</ol>',
            '<ul>$1</ul>',
            '<li>$1</li>',
            '<a href="$1" target="_blank">$1</a>',
        );

        return preg_replace($expressions, $replace, $str);
    }

    public function getName()
    {
        return 'bbcode_extension';
    }
}