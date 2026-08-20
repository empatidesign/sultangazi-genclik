<?php $pager->setSurroundCount(4); ?>
<?php if ($pager->getPageCount() > 1) { ?>
  <div class="container px-6 mx-auto">
    <div class="flex items-center justify-center mx-auto gap-4 text-sm md:text-base">
      <!-- Geri -->
      <?php if ($pager->hasPreviousPage()) { ?>
        <a href="<?php echo $pager->getPreviousPage(); ?>" class="transition-all duration-300 flex items-center gap-2 text-gray-700 hover:text-secondary-400">
          <i class="fal fa-arrow-left"></i>
          <?php echo lang('Web.general.back'); ?>
        </a>
      <?php } ?>

      <div class="flex items-center gap-1.5">
        <?php foreach ($pager->links() as $link) { ?>
          <?php if ($link['active']) { ?>
            <a href="#" class="transition-all duration-300 size-8 grid place-items-center rounded-lg bg-secondary-400 text-white"><?php echo $link['title']; ?></a>
          <?php } else { ?>
            <a href="<?php echo $link['uri']; ?>" class="transition-all duration-300 size-8 grid place-items-center rounded-lg hover:bg-secondary-100 text-gray-700"><?php echo $link['title']; ?></a>
          <?php } ?>
        <?php } ?>
      </div>

      <!-- İleri -->
      <?php if ($pager->hasNextPage()) { ?>
        <a href="<?php echo $pager->getNextPage(); ?>" class="transition-all duration-300 flex items-center gap-2 text-gray-700 hover:text-secondary-400">
          <?php echo lang('Web.general.next'); ?>
          <i class="fal fa-arrow-right"></i>
        </a>
      <?php } ?>
    </div>
  </div>
<?php } ?>