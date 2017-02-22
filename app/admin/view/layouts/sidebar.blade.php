<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <!-- search form -->
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search...">
                <span class="input-group-btn">
                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
            </div>
        </form>
        <!-- /.search form -->
        <!-- sidebar menu: : style can be found in sidebar.less -->
        <ul class="sidebar-menu">
            <li class="header">菜单导航</li>
            <?php if (!empty($menuBar)) : ?>
                <?php $currentUri = substr($currentUri, 1); ?>
                <?php foreach ($menuBar as $menu) : ?>
                    <?php $isCurrentMenu = $this->arraySearch($menu['items'], 'uri', $currentUri); ?>
                    <li class="treeview <?php if ($isCurrentMenu !== false): ?>active<?php endif; ?>">
                        <a href="javascript:;">
                            <i class="fa <?= $menu['style']; ?>"></i> <span><?= $menu['name']; ?></span> <i
                                class="fa fa-angle-left pull-right"></i>
                        </a>
                        <ul class="treeview-menu <?php if ($isCurrentMenu !== false): ?>menu-open<?php endif; ?>">
                            <?php if (!empty($menu['items'])): ?>
                                <?php foreach ($menu['items'] as $item) : ?>
                                    <li <?php if ($currentUri == $item['uri']): ?> class="active" <?php endif; ?> >
                                        <a href="<?php echo $this->urlFor($item['uri'],
                                            isset($item['urlParams']) ? $item['urlParams'] : []); ?>">
                                            <i class="fa fa-circle-o"></i><?= $item['name']; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </section>
    <!-- /.sidebar -->
</aside>