<div id="tl_buttons">
<a href="contao/main.php?do=contaomaps&table=tl_contaomap_layer" class="header_back" title="<?php echo specialchars($GLOBALS['TL_LANG']['MSC']['backBT']); ?>"><?php echo $GLOBALS['TL_LANG']['MSC']['backBT']; ?></a>
</div>
<h2 class="sub_headline"><?php echo $GLOBALS['TL_LANG']['tl_contaomap_layer']['import'][0]; ?></h2><?php echo $this->getMessages(); ?>

<form action="<?php echo ampersand($this->Environment->request, ENCODE_AMPERSANDS); ?>" id="tl_csv_import" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="do" value="<?php echo \Input::getInstance()->get('do'); ?>" />
<input type="hidden" name="table" value="<?php echo \Input::getInstance()->get('table'); ?>" />
<input type="hidden" name="key" value="<?php echo \Input::getInstance()->get('key'); ?>" />
<input type="hidden" name="id" value="<?php echo \Input::getInstance()->get('id'); ?>" />
<input type="hidden" name="token" value="<?php echo $strToken; ?>" />

<div class="tl_tbox">
  <h3><label for="source"><?php echo $GLOBALS['TL_LANG']['tl_contaomap_layer']['source'][0]; ?></label> 
  <a href="contao/files.php" title="<?php echo specialchars($GLOBALS['TL_LANG']['MSC']['fileManager']); ?>" onclick="Backend.getScrollOffset(); this.blur(); Backend.openWindow(this, 750, 500); return false;"><?php echo $this->generateImage('filemanager.gif', $GLOBALS['TL_LANG']['MSC']['fileManager'], 'style="vertical-align:text-bottom;"'); ?></a></h3>
<?php echo $this->objTree->generate(); ?>
<?php if(strlen($GLOBALS['TL_LANG']['tl_contaomap_layer']['source'][1])): ?>
  <p class="tl_help tl_tip"><?php echo $GLOBALS['TL_LANG']['tl_contaomap_layer']['source'][1]; ?></p>
<?php endif; ?>
  <div id="ctrl_removeData" class="tl_checkbox_single_container"><input type="checkbox" name="removeData" id="opt_removeData_0" value="1" class="tl_checkbox" onfocus="Backend.getScrollOffset();" onclick="if (this.checked && !confirm(\'<?php echo sprintf($GLOBALS['TL_LANG']['MSC']['removeDataConfirm'],$objCatalog->name); ?>\')) return false; Backend.getScrollOffset();" /> <label for="opt_removeData_0"><?php echo $GLOBALS['TL_LANG']['tl_contaomap_layer']['removeData'][0]; ?></label></div>
<?php if($GLOBALS['TL_LANG']['tl_contaomap_layer']['removeData'][1]&&$GLOBALS['TL_CONFIG']['showHelp']): ?>
  <p class="tl_help tl_tip"><?php echo $GLOBALS['TL_LANG']['tl_contaomap_layer']['removeData'][1]; ?></p>
<?php endif; ?>
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="import" id="import" class="tl_submit" alt="<?php echo specialchars($GLOBALS['TL_LANG']['tl_contaomap_layer']['import'][1]); ?>" accesskey="s" value="<?php echo specialchars($GLOBALS['TL_LANG']['tl_contaomap_layer']['import'][1]); ?>" /> 
</div>

</div>
</form>
