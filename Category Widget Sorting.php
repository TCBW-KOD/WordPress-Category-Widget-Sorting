<?php

/**
 * 注册自定义器设置和控制
 */
function theme_customize_register($wp_customize) {
    // 向WordPress自定义器添加一个新的部分（区域），用于后续的自定义选项
    $wp_customize->add_section('theme_sort_options', array(
        'title'    => __('Category Widget Sorting', 'your-theme-domain'),  // 标题：分类排序选项
        'priority' => 30,  // 该部分在自定义器中的优先级
    ));

    // 获取所有已注册的菜单
    $menus = wp_get_nav_menus();  // 调用WordPress函数获取所有菜单
    $menu_options = array();  // 初始化菜单选项数组
    foreach ($menus as $menu) {  // 遍历每个菜单
        $menu_options[$menu->term_id] = $menu->name;  // 将菜单ID与菜单名称作为键值对存入数组
    }

    // 向自定义器添加一个设置选项，用于选择菜单
    $wp_customize->add_setting('menu_choice', array(
        'default'   => '',  // 默认值为空
        'transport' => 'refresh',  // 当选项变更时的页面刷新方式
    ));

    // 为上面的设置添加一个控制器，允许用户通过下拉菜单选择一个菜单
    $wp_customize->add_control('menu_choice_control', array(
        'label'    => __('Choose Menu for Sorting', 'your-theme-domain'),  // 标签：选择用于排序的菜单
        'section'  => 'theme_sort_options',  // 控制器所属的部分
        'settings' => 'menu_choice',  // 绑定的设置项
        'type'     => 'select',  // 控制器类型：下拉菜单
        'choices'  => $menu_options,  // 下拉菜单的选项，即之前获取的菜单
    ));

    // 添加一个设置，用于选择分类的排序方式
    $wp_customize->add_setting('sort_order', array(
        'default'   => 'menu_order',  // 默认排序方式为菜单顺序
        'transport' => 'refresh',  // 当选项变更时的页面刷新方式
    ));

    // 为排序方式添加一个控制器，允许用户选择排序方式
    $wp_customize->add_control('sort_order_control', array(
        'label'    => __('Select Category Sort Order', 'your-theme-domain'),  // 标签：选择分类排序方式
        'section'  => 'theme_sort_options',  // 控制器所属的部分
        'settings' => 'sort_order',  // 绑定的设置项
        'type'     => 'select',  // 控制器类型：下拉菜单
        'choices'  => array(
            'menu_order' => __('Menu Order', 'your-theme-domain'),  // 菜单顺序
            'name'       => __('Name', 'your-theme-domain'),  // 按名称排序
            'count'      => __('Count', 'your-theme-domain'),  // 按数量排序
        ),
    ));

    // 添加一个设置，用于选择排序方向（升序或降序）
    $wp_customize->add_setting('sort_direction', array(
        'default'   => 'ASC',  // 默认为升序
        'transport' => 'refresh',  // 当选项变更时的页面刷新方式
    ));

    // 为排序方向添加一个控制器，允许用户选择升序或降序
    $wp_customize->add_control('sort_direction_control', array(
        'label'    => __('Select Sort Direction', 'your-theme-domain'),  // 标签：选择排序方向
        'section'  => 'theme_sort_options',  // 控制器所属的部分
        'settings' => 'sort_direction',  // 绑定的设置项
        'type'     => 'select',  // 控制器类型：下拉菜单
        'choices'  => array(
            'ASC'  => __('Ascending', 'your-theme-domain'),  // 升序
            'DESC' => __('Descending', 'your-theme-domain'),  // 降序
        ),
    ));
}

add_action('customize_register', 'theme_customize_register');  // 将上述函数挂载到'customize_register'动作上

/**
 * 自定义分类排序功能
 */
function customize_category_order_by_menu($args) {
    // 获取用户设置的菜单ID、排序方式和排序方向
    $menu_id = get_theme_mod('menu_choice', '');
    $sort_by = get_theme_mod('sort_order', 'menu_order');
    $sort_direction = get_theme_mod('sort_direction', 'ASC');

    if (!empty($menu_id)) {  // 如果用户已选择一个菜单
        $menu_items = wp_get_nav_menu_items($menu_id);  // 获取该菜单的所有菜单项
        $order = array();  // 初始化排序数组

        foreach ($menu_items as $item) {  // 遍历菜单项
            if ($item->object == 'category') {  // 如果菜单项是分类
                $order[] = $item->object_id;  // 将分类ID添加到排序数组中
            }
        }

        // 根据用户选择的排序方式应用排序参数
        switch ($sort_by) {
            case 'menu_order':  // 如果是按菜单顺序排序
                if (!empty($order)) {
                    $args['include'] = implode(',', $order);  // 将排序数组转换为字符串，设置为包含参数
                    $args['orderby'] = 'include';  // 设置排序字段为包含
                }
                break;
            case 'name':  // 如果是按名称排序
                $args['orderby'] = 'name';  // 设置排序字段为名称
                $args['order'] = $sort_direction;  // 设置排序方向
                break;
            case 'count':  // 如果是按数量排序
                $args['orderby'] = 'count';  // 设置排序字段为数量
                $args['order'] = $sort_direction;  // 设置排序方向
                break;
        }
    }

    return $args;  // 返回修改后的参数
}

add_filter('widget_categories_args', 'customize_category_order_by_menu');  // 将上述函数挂载到'widget_categories_args'过滤器上
