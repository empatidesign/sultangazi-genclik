<?php $pager->setSurroundCount(0); ?>

<?php if ($pager->hasNext()) { ?>
  <a href="<?php echo $pager->getNext(); ?>" class="btn btn--secondary">
    <span><?php echo lang('Pager.loadMore'); ?></span>
  </a>
<?php } ?>
