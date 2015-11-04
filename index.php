<?php if(!defined('IS_CMS')) die();

class OpenGraphMetatags extends Plugin {

    function getContent($value) {

        if(defined("PLUGINADMIN")) {
            $settings_array = $this->settings->toArray();
            if(getRequestValue('saveogp',"post",false)) {
                if(getRequestValue('ogp',"post",false) and is_array(getRequestValue('ogp',"post",false))) {
                    $post_array = getRequestValue('ogp',"post",false);
                    # aufräumen
                    foreach($settings_array as $cat_page => $tmp) {
                        if(strstr($cat_page,FILE_START) !== false and strstr($cat_page,FILE_END) !== false) {
                            if(!array_key_exists($cat_page, $post_array))
                                $this->settings->delete($cat_page);
                        }
                    }
                    foreach($post_array as $cat_page => $value) {
                        if(strlen($value['og_title']) > 2 and strlen($value['og_description']) > 2 ) {
                            $value['og_title'] = htmlentities($value['og_title'],ENT_QUOTES,CHARSET);
                            $value['og_description'] = htmlentities($value['og_description'],ENT_QUOTES,CHARSET);
                            if(!isset($value['og_image']))
                                $value['og_image'] = false;
                            $this->settings->set($cat_page,$value);
                        }
                    }
                }
            } elseif(getRequestValue('clearogp',"post",false)) {
                foreach($settings_array as $cat_page => $tmp) {
                    if("--default--" === $cat_page or (strstr($cat_page,FILE_START) !== false and strstr($cat_page,FILE_END) !== false))
                        $this->settings->delete($cat_page);
                }
            }
            return $this->getAdmin();
        }

        $ogp = "";
        if($value === false) {
            $key = false;
            if($this->settings->keyExists(FILE_START.CAT_REQUEST.":".PAGE_REQUEST.FILE_END)) {
                $key = FILE_START.CAT_REQUEST.":".PAGE_REQUEST.FILE_END;
            } elseif($this->settings->keyExists("--default--")) {
                $key = "--default--";
            }
            if($key) {
                $setting = $this->settings->get($key);
                if(is_array($setting)) {
                    global $CatPage, $CMS_CONF;
                    $host = (defined("HTTP") ? HTTP : "http://").$_SERVER['SERVER_NAME'];
                    $url = $host.$CatPage->get_Href(CAT_REQUEST,PAGE_REQUEST);
                    $locale = substr($CMS_CONF->get("cmslanguage"),0,2)."_".substr($CMS_CONF->get("cmslanguage"),2);
                    if($locale == "en_EN")
                        $locale = "en_GB";
                    $ogp = ''
                        .'<meta property="og:site_name" content="'.$_SERVER['SERVER_NAME'].'" />'."\n"
                        .'<meta property="og:locale" content="'.$locale.'" />'."\n"
                        .'<meta property="og:title" content="'.$setting['og_title'].'" />'."\n"
                        .'<meta property="og:description" content="'.$setting['og_description'].'" />'."\n"
                        .'<meta property="og:type" content="website" />'."\n"
                        .'<meta property="og:url" content="'.$url.'" />'."\n";
                    if($CatPage->exists_File(CAT_REQUEST,$setting['og_image'])) {
                        $ogp .= '<meta property="og:image" content="'.$host.$CatPage->get_srcFile(CAT_REQUEST,$setting['og_image']).'" />'."\n";
                    } elseif($CatPage->exists_File($CMS_CONF->get("defaultcat"),$setting['og_image'])) {
                        $ogp .= '<meta property="og:image" content="'.$host.$CatPage->get_srcFile($CMS_CONF->get("defaultcat"),$setting['og_image']).'" />'."\n";
                    }
               }
            }
        }
        return $ogp;
    }

    function getConfig() {
        if(IS_ADMIN and $this->settings->get("plugin_first") !== "true") {
            $this->settings->set("plugin_first","true");
        }
        $config = array();
        $config["--admin~~"] = array(
            "buttontext" => "Bearbeiten",
            "description" => "Open Graph Metatags für jede Inhaltsseite:",
            "datei_admin" => "index.php"
        );
        return $config;
    }

    function getInfo() {
            $info = array(
            // Plugin-Name + Version
            "<b>OpenGraphMetatags</b> Revision 2",
            // moziloCMS-Version
            "2.0",
            // Kurzbeschreibung nur <span> und <br /> sind erlaubt
            'Das Plugin Erzeugt folgende Open Graph Metatags:<br />
            site_name = Domain<br />
            locale = die Spracheinstelung aus dem Admin Einstellungen<br />
            title = frei wählbar für jede Inhaltsseite<br />
            description = frei wählbar für jede Inhaltsseite<br />
            type = website<br />
            url = URL der Aktiven Inhaltsseite<br />
            image = frei wählbar für jede Inhaltsseite. Es sind nur Bilder aus der zugehörigen Kategorie wählbar.<br />
            <br />
            Damit die Metadaten ausgewertet werden, ist der richtige DOCTYPE nötig.<br />
            Beispiel:<br />
            <!-- das sol angeblich valide sein
            &lt;!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd"&gt;
            &lt;html xmlns="http://www.w3.org/1999/xhtml" version="XHTML+RDFa 1.0" xml:lang="en" lang="de" xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb= "http://www.facebook.com/2008/fbml"&gt;
            -->
            &lt;!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"<br />
                "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"&gt;<br />
            &lt;html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"  xmlns:fb="http://www.facebook.com/2008/fbml" xmlns:og="http://opengraphprotocol.org/schema/"&gt;<br />
            <br />
            Der Platzhalter {OpenGraphMetatags} muss im Template in der template.html zwischen &lt;head&gt; und &lt;/head&gt; Eingetragen werden.',
            // Name des Autors
            "stefanbe",
            // Download-URL
            array("http://www.mozilo.de/forum/index.php?action=media","Templates und Plugins"),
            // Platzhalter für die Selectbox in der Editieransicht 
            // - ist das Array leer, erscheint das Plugin nicht in der Selectbox
            array("{OpenGraphMetatags}" => "Einfügen in den HEAD Bereich des Templates")
        );
        return $info;
    }

    function getAdmin() {
        global $CatPage;
        $lang = array("og_title" => "Titel","og_description" => "Beschreibung","og_image" => "Bild");
        $description = array();
        $og_title = array();
        if($this->settings->get("og_description") and is_array($this->settings->get("og_description")))
            $og_description = $this->settings->get("og_description");
        if($this->settings->get("og_title") and is_array($this->settings->get("og_title")))
            $og_title = $this->settings->get("og_title");

        $html = '<div id="admin-ogp">'
            .'<form name="allentries" action="'.URL_BASE.ADMIN_DIR_NAME.'/index.php" method="POST">'
            .'<input type="hidden" name="pluginadmin" value="'.PLUGINADMIN.'" />'
            .'<input type="hidden" name="action" value="'.ACTION.'" />'
            .'<div id="admin-ogp-menu" class="ui-widget-content ui-corner-all">'
            .'<input type="submit" class="admin-ogp-submit" name="saveogp" value="Speichern" /> '
            .'<input type="submit" class="admin-ogp-submit" name="clearogp" value="Alle Einträge Löschen" /><br />'
            .'</div>'
            .'<div id="admin-ogp-content">'
            .'<ul id="admin-ogp-content-ul" class="mo-ul">';
        $keyToCheck = "--default--";
        $html .= '<li class="mo-li ui-widget-content ui-corner-all ui-state-highlight">'
            .'<div class="admin-ogp-cat">'."Default Eintrag".'</div>'
            .'<ul class="mo-in-ul-ul">';
        $html .= '<li class="mo-in-ul-li mo-inline ui-widget-content ui-corner-all ui-helper-clearfix">';
        $in_og_description = '';
        $in_og_title = '';
        $in_og_image = false;
        if($this->settings->keyExists($keyToCheck)) {
            $setting = $this->settings->get($keyToCheck);
            if(is_array($setting)) {
                $in_og_description = $setting['og_description'];
                $in_og_title = $setting['og_title'];
                $in_og_image = $setting['og_image'];
            }
        }
        global $CMS_CONF;
        $html .= '<span>'.$lang['og_title'].':</span> <input class="admin-ogp-in" name="ogp['.$keyToCheck.'][og_title]" type="text" size="100" maxlength="255" value="'.$in_og_title.'" /><br />'
            .'<span class="vertical-top">'.$lang['og_description'].':</span><textarea name="ogp['.$keyToCheck.'][og_description]" cols="100" rows="4">'.$in_og_description.'</textarea>'
            .'<span>'.$lang['og_image'].':</span>'.$this->adminFilesSelectbox($CMS_CONF->get("defaultcat"),$keyToCheck,$in_og_image)
            .'</li>'
            .'</ul></li>';

        foreach ($CatPage->get_CatArray(false,false,array(EXT_PAGE,EXT_HIDDEN)) as $cat) {
            $html .= '<li class="mo-li ui-widget-content ui-corner-all"><div class="admin-ogp-cat">'.$CatPage->get_HrefText($cat,false).'</div>'
            .'<ul class="mo-in-ul-ul">';
            foreach ($CatPage->get_PageArray($cat,array(EXT_PAGE,EXT_HIDDEN),true) as $page) {
                $html .= '<li class="mo-in-ul-li mo-inline ui-widget-content ui-corner-all ui-helper-clearfix"><b>'.$CatPage->get_HrefText($cat,$page).'</b><br />';
                $keyToCheck = FILE_START.$cat.":".$page.FILE_END;
                $in_og_description = '';
                $in_og_title = '';
                $in_og_image = false;
                if($this->settings->keyExists($keyToCheck)) {
                    $setting = $this->settings->get($keyToCheck);
                    if(is_array($setting)) {
                        $in_og_description = $setting['og_description'];
                        $in_og_title = $setting['og_title'];
                        $in_og_image = $setting['og_image'];
                    }
                }
                $html .= '<span>'.$lang['og_title'].':</span> <input class="admin-ogp-in" name="ogp['.$keyToCheck.'][og_title]" type="text" size="100" maxlength="255" value="'.$in_og_title.'" /><br />'
                    .'<span class="vertical-top">'.$lang['og_description'].':</span><textarea name="ogp['.$keyToCheck.'][og_description]" cols="100" rows="4">'.$in_og_description.'</textarea>'
                    .'<span>'.$lang['og_image'].':</span>'.$this->adminFilesSelectbox($cat,$keyToCheck,$in_og_image)
                    .'</li>';
            }
            $html .= '</ul></li>';
        }
        return $html.'</ul></div></form></div>';
    }

    function adminFilesSelectbox($cat,$keyToCheck,$in_og_image) {
        global $specialchars;
        global $CatPage;
        global $ALOWED_IMG_ARRAY;
        $select = '<select name="ogp['.$keyToCheck.'][og_image]" class="overviewselect" title="'.getLanguageValue("files_button",true).':">';
        $select .= '<option value="'."false".'">'."Kein Bild"."</option>";
        $cleancatname = $CatPage->get_HrefText($cat,false);
        foreach($CatPage->get_FileArray($cat,$ALOWED_IMG_ARRAY) as $current_file) {
            $selected = '';
            if($current_file == $in_og_image)
                $selected = ' selected="selected"';
            $select .= '<option value="'.$specialchars->rebuildSpecialChars($current_file, true, true).'"'.$selected.'>'.$specialchars->rebuildSpecialChars($current_file, false, true)."</option>";
        }
        $select .= "</select>";
        return $select;
    }
}
?>