<?php
namespace Grav\Plugin;

use \Grav\Common\Plugin;
use \Grav\Common\Grav;
use \Grav\Common\Page\Page;

class AcePlugin extends Plugin
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPageInitialized' => ['onPageInitialized', 0]
        ];
    }

    /**
     * Initialize configuration
     */
    public function onPageInitialized()
    {
        if ($this->isAdmin()) {
            $this->active = false;
            return;
        }

        $defaults = (array) $this->config->get('plugins.ace');

        /** @var Page $page */
        $page = $this->grav['page'];
        if (isset($page->header()->ace)) {
            $this->config->set('plugins.ace', array_merge($defaults, $page->header()->ace));
        }
        if ($this->config->get('plugins.ace.enabled')) {
            $this->enable([
                'onTwigSiteVariables' => ['onTwigSiteVariables', 0]
            ]);
        }
    }

    /**
     * if enabled on this page, load the JS + CSS theme.
     */
    public function onTwigSiteVariables()
    {
        $theme = $this->config->get('plugins.ace.theme') ?: 'monokai';
        $lang = $this->config->get('plugins.ace.language') ?: 'javascript';
        $init = '
            var aceEditors = [];
            $(document).ready(function() {
                var iAce = 0;
                $(".grav-ace").each(function(){
                    var lang = $(this).data("lang") || "'.$lang.'";
                    var theme = $(this).data("theme") || "'.$theme.'";
                    while(true){
                        var id = "grav-ace-"+iAce;
                        if($("#"+id).length == 0){
                            $(this).attr("id", id);
                            var l = aceEditors.length;
                            $(this).data("editor-index", l);
                            aceEditors[l] = ace.edit(id);
                            aceEditors[l-1].setTheme("ace/theme/"+theme);
                            aceEditors[l-1].getSession().setMode("ace/mode/"+lang);
                        }
                        else{ iAce++; }
                    }
                    iAce++;
                })
            });';
        
//        $this->grav['assets']->addCss('plugin://ace/css/'.$theme.'.css');
        $this->grav['assets']->addJs('https://raw.githubusercontent.com/ajaxorg/ace-builds/master/src-noconflict/ace.js');
        $this->grav['assets']->addInlineJs($init);
    }
}
