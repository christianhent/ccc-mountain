<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content Category Alternate Layout with ExtraField Fun
 *
 * @copyright   Copyright (C) 2017 Elisa Foltyn - Coolcat Creations.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');


$dispatcher = JEventDispatcher::getInstance();

//$this->category->text = $this->category->description;
$dispatcher->trigger('onContentPrepare', array($this->category->extension . '.categories', &$this->category, &$this->params, 0));
//$this->category->description = $this->category->text;

$results           = $dispatcher->trigger('onContentAfterTitle', array($this->category->extension . '.categories', &$this->category, &$this->params, 0));
$afterDisplayTitle = trim(implode("\n", $results));

$results              = $dispatcher->trigger('onContentBeforeDisplay', array($this->category->extension . '.categories', &$this->category, &$this->params, 0));
$beforeDisplayContent = trim(implode("\n", $results));

$results             = $dispatcher->trigger('onContentAfterDisplay', array($this->category->extension . '.categories', &$this->category, &$this->params, 0));
$afterDisplayContent = trim(implode("\n", $results));

?>

<?php
$introcount = count($this->intro_items);
$counter    = 0;
?>

<?php if (!empty($this->intro_items)) : ?>
	<?php foreach ($this->intro_items as $key => &$item) : ?>
		<?php

		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		$this->item = &$item;


		/*Shortcut for params*/
		$params = $this->item->params;

		/*check if the user is allowed to edit */
		$canEdit = $this->item->params->get('access-edit');

		/* check for the info / automatic position */

		$info = $params->get('info_block_position', 0);

		$images = json_decode($this->item->images);

		/*check if there are customFields in this article */
		$showExtras = false;
		foreach ($this->item->jcfields as $field)
		{
			if ($field->value)
				$showExtras = true;
		}

		if (!$this->item->jcfields)
		{
			$showExtras = false;
		}

		// Check if associations are implemented. If they are, define the parameter.
		$assocParam = (JLanguageAssociations::isEnabled() && $params->get('show_associations'));

		?>


		<?php if ($this->item->state == 0 || strtotime($this->item->publish_up) > strtotime(JFactory::getDate())
			|| ((strtotime($this->item->publish_down) < strtotime(JFactory::getDate())) && $this->item->publish_down != JFactory::getDbo()->getNullDate())
		) : ?>
			<div class="system-unpublished">
		<?php endif; ?>

		<!-- Page Content -->
		<section
			class="content-section-<?php echo($counter % 2 ? "a" : "b"); ?> <?php echo $this->item->state == 0 ? ' system-unpublished' : null; ?>"
			itemprop="blogPost" itemscope itemtype="https://schema.org/BlogPosting">

			<div class="container">

				<div class="row">
					<?php /* If customFields are not filled, content will expand to the full width */ ?>
					<div
						class="<?php if ($showExtras || !empty($images->image_intro)) : ?>col-lg-7 <?php echo($counter % 2 ? "" : "push-lg-5") ?>	<?php endif; ?>
						<?php if (($showExtras == false) && empty($images->image_intro)) : ?>col-lg-12<?php endif; ?>">
						<hr class="section-heading-spacer">
						<div class="clearfix"></div>

						<?php echo JLayoutHelper::render('joomla.content.blog_style_default_item_title', $this->item); ?>

						<div class="lead">

							<?php if ($canEdit || $params->get('show_print_icon') || $params->get('show_email_icon')) : ?>
								<?php echo JLayoutHelper::render('joomla.content.icons', array('params' => $params, 'item' => $this->item, 'print' => false)); ?>
							<?php endif; ?>

							<?php if ($params->get('show_tags') && !empty($this->item->tags->itemTags)) : ?>
								<?php echo JLayoutHelper::render('joomla.content.tags', $this->item->tags->itemTags); ?>
							<?php endif; ?>

							<?php $useDefList = ($params->get('show_modify_date') || $params->get('show_publish_date') || $params->get('show_create_date')
								|| $params->get('show_hits') || $params->get('show_category') || $params->get('show_parent_category') || $params->get('show_author') || $assocParam); ?>

							<?php if ($useDefList && ($info == 0 || $info == 2)) : ?>
								<?php // Todo: for Joomla4 joomla.content.info_block.block can be changed to joomla.content.info_block ?>
								<?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $this->item, 'params' => $params, 'position' => 'above')); ?>
							<?php endif; ?>


							<?php if (!$params->get('show_intro')) : ?>
								<?php // Content is generated by content plugin event "onContentAfterTitle" ?>
								<?php echo $this->item->event->afterDisplayTitle; ?>
							<?php endif; ?>
							<?php // Content is generated by content plugin event "onContentBeforeDisplay" ?>
							<?php echo $this->item->event->beforeDisplayContent; ?>


							<?php if ($useDefList && ($info == 1 || $info == 2)) : ?>
								<?php // Todo: for Joomla4 joomla.content.info_block.block can be changed to joomla.content.info_block ?>
								<?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $this->item, 'params' => $params, 'position' => 'below')); ?>
							<?php endif; ?>

							<?php echo $this->item->introtext; ?>

							<?php if ($params->get('show_readmore') && $this->item->readmore) :
								if ($params->get('access-view')) :
									$link = JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language));
								else :
									$menu   = JFactory::getApplication()->getMenu();
									$active = $menu->getActive();
									$itemId = $active->id;
									$link   = new JUri(JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId, false));
									$link->setVar('return', base64_encode(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language)));
								endif; ?>

								<?php echo JLayoutHelper::render('joomla.content.readmore', array('item' => $this->item, 'params' => $params, 'link' => $link)); ?>

							<?php endif; ?>

						</div>
					</div>

					<?php /* show only if customFields are filled */ ?>
					<?php if (($showExtras) || !empty($images->image_intro)) : ?>
						<div class="col-lg-5 <?php echo($counter % 2 ? "" : "pull-lg-7") ?> ">

							<?php echo JLayoutHelper::render('joomla.content.intro_image', $this->item); ?>

							<?php if ($showExtras) : ?>

								<ul class="nav nav-tabs" role="tablist">
									<?php $tabCounter = 0;
									foreach ($this->item->jcfields as $field) :
										if (!empty($field->value)) : ?>
											<li class="nav-item">
												<a class="nav-link <?php echo $tabCounter <= 0 ? 'active' : ''; ?>"
												   data-toggle="tab"
												   href="#tab-<?php echo $field->name; ?>-<?php echo $counter; ?>"
												   role="tab"><?php echo JTEXT::_($field->label); ?>
												</a>
											</li>

											<?php $tabCounter++;
										endif;
									endforeach; ?>
								</ul>


								<div class="tab-content">
									<?php $contentCounter = 0;
									foreach ($this->item->jcfields as $field) :
										if (!empty($field->value)) : ?>

											<div
												class="tab-pane fade <?php echo $contentCounter <= 0 ? 'show active' : ''; ?>"
												id="tab-<?php echo $field->name; ?>-<?php echo $counter; ?>"
												role="tabpanel">
												<?php echo $field->value; ?>
											</div>

											<?php $contentCounter++;
										endif;
									endforeach; ?>

								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<?php /* CustomField Area End */ ?>
				</div> <!-- /.row -->
			</div><!-- /.container -->
		</section>


		<?php if ($this->item->state == 0 || strtotime($this->item->publish_up) > strtotime(JFactory::getDate())
			|| ((strtotime($this->item->publish_down) < strtotime(JFactory::getDate())) && $this->item->publish_down != JFactory::getDbo()->getNullDate())
		) : ?>
			</div>
		<?php endif; ?>

		<?php // Content is generated by content plugin event "onContentAfterDisplay" ?>
		<?php echo $this->item->event->afterDisplayContent; ?>

		<!-- end item -->
		<?php $counter++; ?>

	<?php endforeach; ?>

	<?php if (($this->params->def('show_pagination', 1) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->get('pages.total') > 1)) : ?>
		<?php if ($this->params->def('show_pagination_results', 1)) : ?>
			<div class="container">
				<div class="row justify-content-center">
					<?php echo $this->pagination->getPagesCounter(); ?>
				</div>
			</div>
		<?php endif; ?>


				<?php echo $this->pagination->getPagesLinks(); ?>

	<?php endif; ?>

<?php endif; ?>


