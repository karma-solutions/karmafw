<?php

namespace KarmaFW\App\Middlewares;

use \KarmaFW\Http\Request;
use \KarmaFW\Http\Response;


class MinimifierHtml
{
    protected $minimify_html;
    protected $minimify_external_js;
    protected $minimify_external_css;
    protected $content_types;

    
    public function __construct($minimify_html=true, $minimify_external_js=true, $minimify_external_css=true)
    {
        $this->minimify_html = $minimify_html;
        $this->minimify_external_js = $minimify_external_js;
        $this->minimify_external_css = $minimify_external_css;
    }


    public function __invoke(Request $request, Response $response, callable $next)
    {
        $response = $next($request, $response);

        
        $content_type = $response->getContentType();
        $content_type_short = explode(';', $content_type)[0];

        $content_types = [
            'text/html',
        ];

        if (empty($content_types) || ! in_array($content_type_short, $content_types)) {
            // restriction to the selected content_types
            return $response;
        }


        if ($this->minimify_external_js || $this->minimify_external_css) {
            // modification à la volée des liens CSS & JS
            $content = $response->getContent();

            if ($this->minimify_external_css) {
                // modify CSS link files in HTML content
                preg_match_all('#<link[^>]+"(/assets/css/[^">]+.css)"[^>]*>#', $content, $regs);
                $css_files = $regs[1];
                $suffix = '.phpmin.css';
                
                foreach ($css_files as $css_file) {
                    if (substr($css_file, -8) != '.min.css' && substr($css_file, -11) != '.phpmin.css') {
                        $replacement = '\1' . $css_file . $suffix . '\2';
                        $content = preg_replace('#(<link [^>]+")' . preg_quote($css_file) . '("[^>]*>)#', $replacement, $content);
                    }
                }
            }

            if ($this->minimify_external_js) {
                // modify JS link files in HTML content
                preg_match_all('#<script[^>]+"(/assets/js/[^">]+.js)"[^>]*>#', $content, $regs);
                $js_files = $regs[1];
                $suffix = '.phpmin.js';
                
                foreach ($js_files as $js_file) {
                    if (substr($js_file, -7) != '.min.js' && substr($js_file, -10) != '.phpmin.js') {
                        $replacement = '\1' . $js_file . $suffix . '\2';
                        $content = preg_replace('#(<script[^>]+")' . preg_quote($js_file) . '("[^>]*>)#', $replacement, $content);
                    }
                }
            }

            $response->setBody($content);
        }


        if ($this->minimify_html) {
            // minimify HTML
            $content = $response->getContent();
            $content_length = $response->getContentLength();

            $content_minimified = self::minify_html($content);
            $response->setBody($content_minimified);
            $content_minimified_length = $response->getContentLength();

            // add information headers
            $response->addHeader('X-HTML-Unminimified-Content-Length', $content_length);
            $response->addHeader('X-HTML-Minimified-Content-Length', $content_minimified_length);
        }

        return $response;
    }



    // HTML Minifier (source: https://gist.github.com/Rodrigo54/93169db48194d470188f )
    public static function minify_html($input) {
        if(trim($input) === "") return $input;
        // Remove extra white-space(s) between HTML attribute(s)
        $input = preg_replace_callback('#<([^\/\s<>!]+)(?:\s+([^<>]*?)\s*|\s*)(\/?)>#s', function($matches) {
            return '<' . $matches[1] . preg_replace('#([^\s=]+)(\=([\'"]?)(.*?)\3)?(\s+|$)#s', ' $1$2', $matches[2]) . $matches[3] . '>';
        }, str_replace("\r", "", $input));
        // Minify inline CSS declaration(s)
        if(strpos($input, ' style=') !== false) {
            $input = preg_replace_callback('#<([^<]+?)\s+style=([\'"])(.*?)\2(?=[\/\s>])#s', function($matches) {
                return '<' . $matches[1] . ' style=' . $matches[2] . MinimifierCss::minify_css($matches[3]) . $matches[2];
            }, $input);
        }
        if(strpos($input, '</style>') !== false) {
          $input = preg_replace_callback('#<style(.*?)>(.*?)</style>#is', function($matches) {
            return '<style' . $matches[1] .'>'. MinimifierCss::minify_css($matches[2]) . '</style>';
          }, $input);
        }
        if(strpos($input, '</script>') !== false) {
          $input = preg_replace_callback('#<script(.*?)>(.*?)</script>#is', function($matches) {
            return '<script' . $matches[1] .'>'. MinimifierJs::minify_js($matches[2]) . '</script>';
          }, $input);
        }

        return preg_replace(
            array(
                // t = text
                // o = tag open
                // c = tag close
                // Keep important white-space(s) after self-closing HTML tag(s)
                '#<(img|input)(>| .*?>)#s',
                // Remove a line break and two or more white-space(s) between tag(s)
                '#(<!--.*?-->)|(>)(?:\n*|\s{2,})(<)|^\s*|\s*$#s',
                '#(<!--.*?-->)|(?<!\>)\s+(<\/.*?>)|(<[^\/]*?>)\s+(?!\<)#s', // t+c || o+t
                '#(<!--.*?-->)|(<[^\/]*?>)\s+(<[^\/]*?>)|(<\/.*?>)\s+(<\/.*?>)#s', // o+o || c+c
                '#(<!--.*?-->)|(<\/.*?>)\s+(\s)(?!\<)|(?<!\>)\s+(\s)(<[^\/]*?\/?>)|(<[^\/]*?\/?>)\s+(\s)(?!\<)#s', // c+t || t+o || o+t -- separated by long white-space(s)
                '#(<!--.*?-->)|(<[^\/]*?>)\s+(<\/.*?>)#s', // empty tag
                '#<(img|input)(>| .*?>)<\/\1>#s', // reset previous fix
                '#(&nbsp;)&nbsp;(?![<\s])#', // clean up ...
                '#(?<=\>)(&nbsp;)(?=\<)#', // --ibid
                // Remove HTML comment(s) except IE comment(s)
                '#\s*<!--(?!\[if\s).*?-->\s*|(?<!\>)\n+(?=\<[^!])#s'
            ),
            array(
                '<$1$2</$1>',
                '$1$2$3',
                '$1$2$3',
                '$1$2$3$4$5',
                '$1$2$3$4$5$6$7',
                '$1$2$3',
                '<$1$2',
                '$1 ',
                '$1',
                ""
            ),
        $input);
    }
}
