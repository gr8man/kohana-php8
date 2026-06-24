<h4>Tags</h4>
<ul class="tags">
<?php foreach ($tags as $name => $set): ?>
<li><?php echo ucfirst((string) $name).($set?' - '.implode(', ',$set):''); ?>
<?php endforeach ?>
</ul>