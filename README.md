# MageProfis_Hreflang
creates Hreflang tag for store views.

##Attention
store ids & Lang codes have to be maintained manually in: 
app/code/local/MageProfis/Hreflang/Block/Hreflang.php

from Line: 36
<pre>
public function getLangCodeByStoreId($id)
    {
        $ids = array(
            1 => "de-DE",
            2 => "en-US"
        );
        return $ids[$id];
    }
</pre>

###to Do:
<ul>
<li>Create Setting for Store- and Langcode mapping so that it can be maintained via Backend...</li>
</ul>
