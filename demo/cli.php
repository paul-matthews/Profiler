<?php
require_once(dirname(__FILE__) . '/../lib/Profiler.php');

$profiler = new Profiler();

$token_page = $profiler->start('Start Page');

// Do may things...

    // assign memory
$foo = array();
for($i = 0; $i < 100; $i++) {
    $foo[$i] = <<<EOF
    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec pharetra neque nec nisl auctor eu consectetur nisl porta. Nullam at odio ipsum, sed rutrum nulla. Nulla imperdiet, dolor at elementum rhoncus, lacus libero fermentum neque, sit amet condimentum sem est ut libero. Suspendisse dictum pharetra nisl sit amet facilisis. Quisque consectetur hendrerit interdum. Proin pellentesque venenatis est ac dictum. Suspendisse ut vehicula leo. Cras tempor diam aliquam mi convallis at aliquam erat facilisis. Maecenas mauris odio, cursus vel commodo quis, dignissim vitae nisl. Donec tempus mi in diam hendrerit pellentesque. Nulla quis nunc risus. Sed nec dolor ipsum, in rutrum mauris. Integer lacinia vehicula varius.

    Praesent egestas auctor magna consequat ultrices. Donec nec aliquam lacus. Mauris placerat, erat at varius rhoncus, nibh augue blandit odio, at vestibulum ante leo eu dui. Integer venenatis pharetra volutpat. Suspendisse ultricies mi at risus sagittis ac blandit eros ultrices. Quisque at consequat risus. Pellentesque non aliquam nisi. Donec interdum justo sit amet felis convallis pretium et bibendum urna. Ut fermentum purus eget lectus aliquet egestas. Aenean at nisi est, vitae venenatis urna. Quisque a orci in lacus sollicitudin eleifend nec eget sapien. Sed massa leo, tempor ut pretium eget, tempus a nunc. Sed faucibus urna non lectus tincidunt rhoncus eget ut purus. Pellentesque sagittis nisi non odio cursus nec ultrices libero molestie. Curabitur dolor sapien, volutpat nec vehicula eget, consectetur nec eros.

    Vestibulum molestie ultricies molestie. Sed purus nisi, laoreet non tristique quis, accumsan non nisi. Donec nec risus nec dui sagittis feugiat. Ut imperdiet ultrices tortor, non mattis nulla bibendum condimentum. Donec adipiscing lacinia blandit. Suspendisse venenatis, magna in luctus eleifend, enim nulla euismod nibh, ac dictum magna lorem non elit. Suspendisse quis enim consequat arcu vestibulum rutrum. Sed metus elit, posuere ut fermentum quis, vulputate nec lorem. Quisque bibendum enim id elit egestas ut ultricies elit fermentum. Ut a dapibus lorem.

    Nullam pulvinar malesuada nibh, eu tincidunt dolor sodales nec. Suspendisse facilisis sagittis venenatis. Donec dignissim tempor nibh eget laoreet. Nam vitae augue elit. Sed vitae eros sapien. Sed ut tincidunt risus. Vestibulum egestas massa vel dolor feugiat cursus. Praesent tellus urna, ultricies ut tempor feugiat, volutpat pretium est. Suspendisse potenti. Suspendisse quis tellus a eros sagittis ullamcorper. Curabitur id ultricies nunc. Morbi ultricies venenatis felis. Mauris justo est, pulvinar id sagittis non, aliquet varius est. Quisque ipsum enim, luctus ut scelerisque et, dapibus quis lectus.

    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam mi lorem, venenatis vitae gravida at, fermentum in neque. Duis non mauris eget est semper gravida quis vitae nisi. Phasellus pharetra tristique massa, id bibendum libero ullamcorper eu. Pellentesque a lacus neque. Donec ante leo, volutpat ac mattis a, porttitor sit amet ligula. Nam commodo aliquam velit, vitae volutpat velit fringilla mollis. Sed auctor orci vitae ante consequat molestie. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Etiam neque eros, scelerisque et tempor non, eleifend eget turpis. Vestibulum iaculis ligula in augue eleifend suscipit ultricies erat faucibus. Quisque vulputate eleifend risus eu malesuada. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Donec scelerisque diam non lacus congue auctor. Nulla commodo odio iaculis mi tristique sodales. Suspendisse potenti. Nulla nec neque nulla. Ut id nulla nisi, nec gravida quam. Praesent ornare lorem at lorem egestas non dictum orci condimentum.

EOF;

}


$token_sleep = $profiler->start('Start Timer');

sleep(2);

$profiler->stop($token_sleep);

$profiler->stop($token_page);

foreach ($profiler as $profile) {
    echo sprintf("%s: %0.3f %d\n",
        $profile['name'], $profile['duration'], $profile['usage_mem']);
}
