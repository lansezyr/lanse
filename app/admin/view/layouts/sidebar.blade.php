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
            @if (!empty($menuBar))
                @foreach ($menuBar as $menu)
                    <li class="treeview @if(\Root\Library\Util\ArrUtil::arraySearch($menu['item'], 'uri', $currentUri))
                            active
                            @endif">
                        <a href="javascript:">
                            <i class="fa {{$menu['style']}}"></i> <span>{{$menu['name']}}</span> <i
                                    class="fa fa-angle-left pull-right"></i>
                        </a>
                        <ul class="treeview-menu">
                            @if (!empty($menu['items']))
                                @foreach ($menu['items'] as $item)
                                    <li @if ($currentUri == $item['uri'])
                                        class="active"
                                            @endif >
                                        <a href="/{{$item['uri']}}}">
                                            <i class="fa fa-circle-o"></i>{{$item['name']}}
                                        </a>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </li>
                @endforeach
            @endif
        </ul>
    </section>
    <!-- /.sidebar -->
</aside>