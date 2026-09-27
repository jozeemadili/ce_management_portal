<?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount($name, $params)->html();
} elseif ($_instance->childHasBeenRendered('1ygl7hX')) {
    $componentId = $_instance->getRenderedChildComponentId('1ygl7hX');
    $componentTag = $_instance->getRenderedChildComponentTagName('1ygl7hX');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('1ygl7hX');
} else {
    $response = \Livewire\Livewire::mount($name, $params);
    $html = $response->html();
    $_instance->logRenderedChild('1ygl7hX', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?>
<?php /**PATH /Users/josephatwilliammadili/Desktop/new3/PROJECTS/New LARAVEL PROJECTS/ce_applications/ce_management_portal/vendor/livewire/livewire/src/Testing/../views/mount-component.blade.php ENDPATH**/ ?>