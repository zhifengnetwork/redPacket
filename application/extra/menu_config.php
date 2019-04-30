<?php
return [
      //数据分析
      'baobiao'      => [
        'id'    => 60000,
        'title' => '财务管理',
        'sort'  => 2,
        'url'   => 'total/index',
        'hide'  => 1,
        'icon'  => 'glyphicon glyphicon-file',
        'child' => [
            [
                'id'    => 60100,
                'title' => '财务管理',
                'sort'  => 1,
                'url'   => '',
                'hide'  => 1,
                'child' => [
                    [
                        'id'    => 60101,
                        'title' => '余额记录',
                        'sort'  => 1,
                        'url'   => 'total/balance_logs',
                        'hide'  => 1,
                    ],
                    [
                        'id'    => 60102,
                        'title' => '积分记录',
                        'sort'  => 1,
                        'url'   => 'total/integral_logs',
                        'hide'  => 1,
                    ],
                ],

            ],
            [
                'id'    => 60200,
                'title' => '余额体现',
                'sort'  => 2,
                'url'   => 'total/finance',
                'hide'  => 1,
                'child' => [
                    [
                        'id'    => 60201,
                        'title' => '待审核',
                        'sort'  => 1,
                        'url'   => '',
                        'hide'  => 1,
                    ],
                    [
                        'id'    => 60202,
                        'title' => '通过审批',
                        'sort'  => 1,
                        'url'   => '',
                        'hide'  => 1,
                    ],
                    [
                        'id'    => 60203,
                        'title' => '不通过审批',
                        'sort'  => 1,
                        'url'   => '',
                        'hide'  => 1,
                    ],
                    [
                        'id'    => 60204,
                        'title' => '余额提现设置',
                        'sort'  => 1,
                        'url'   => '',
                        'hide'  => 1,
                    ],
                   
                ],
            ],
           
         ],
     ],



    
    
    //配置管理
    'pz_config' => [
        'id'    => 50000,
        'title' => '配置管理',
        'sort'  => 8,
        'url'   => 'config/index',
        'hide'  => 1,
        'icon'  => 'glyphicon glyphicon-link',
        'child' => [
            [
                'id'    => 50100,
                'title' => '首页轮播图',
                'sort'  => 1,
                'url'   => 'advertisement/index',
                'hide'  => 1,
                'icon'  => 'fa-th-large',
                'child' => [
                    [
                        'id'    => 50101,
                        'title' => '首页轮播图',
                        'sort'  => 1,
                        'url'   => 'advertisement/index',
                        'hide'  => 1,
                    ],
                    [
                        'id'    => 50201,
                        'title' => '轮播图编辑',
                        'sort'  => 1,
                        'url'   => 'advertisement/edit',
                        'hide'  => 0,
                    ],
                ],
            ],
            [
                'id'    => 50200,
                'title' => '配送方式',
                'sort'  => 2,
                'url'   => 'delivery/index',
                'icon'  => 'fa-th-large',
                'hide'  => 1,
            ],
            [
                'id'    => 50300,
                'title' => '客服设置',
                'sort'  => 3,
                'url'   => 'config/get_config',
                'icon'  => 'fa-th-large',
                'hide'  => 1,
            ],
           
        ],
    ],



    //系统设置
    'sys_config'      => [
        'id'    => 210000,
        'title' => '系统设置',
        'sort'  => 21,
        'url'   => 'auths/auth_group',
        'hide'  => 1,
        'icon'  => 'glyphicon glyphicon-cog',
        'child' => [
            [
                'id'    => 210100,
                'title' => '管理员',
                'sort'  => 1,
                'url'   => 'mguser/index',
                'hide'  => 1,
                'icon'  => 'fa-th-large',
                'child' => [
                   
                    [
                        'id'    => 210101,
                        'title' => '编辑',
                        'sort'  => 1,
                        'url'   => 'mguser/edit',
                        'hide'  => 0,
                    ],
                    [
                        'id'    => 210102,
                        'title' => '用户授权',
                        'sort'  => 2,
                        'url'   => '',
                        'hide'  => 0,
                    ],
                    [
                        'id'    => 210103,
                        'title' => '修改密码',
                        'sort'  => 3,
                        'url'   => 'mguser/update_pwsd',
                        'hide'  => 0,
                    ],
                    [
                        'id'    => 210104,
                        'title' => '管理人员',
                        'sort'  => 1,
                        'url'   => 'mguser/index',
                        'hide'  => 1,
                    ],
                ],
            ],
            [
                'id'    => 210200,
                'title' => '权限管理',
                'sort'  => 2,
                'url'   => 'auths/auth_group',
                'hide'  => 1,
                'icon'  => 'fa-th-large',
                'child' => [
                  
                    [
                        'id'    => 210201,
                        'title' => '编辑分组',
                        'sort'  => 1,
                        'url'   => 'auths/edit',
                        'hide'  => 0,
                    ],
                    [
                        'id'    => 210202,
                        'title' => '分组授权',
                        'sort'  => 2,
                        'url'   => 'auths/manage_auths',
                        'hide'  => 0,
                    ],
                    [
                        'id'    => 210203,
                        'title' => '授权用户',
                        'sort'  => 3,
                        'url'   => 'auths/auth_user',
                        'hide'  => 0,
                    ],
                    [
                        'id'    => 210204,
                        'title' => '权限管理',
                        'sort'  => 1,
                        'url'   => 'auths/auth_group',
                        'hide'  => 1,
                    ],
                ],
            ],
            [
                'id'    => 210300,
                'title' => '菜单',
                'sort'  => 3,
                'url'   => 'menu/index',
                'hide'  => 1,
                'icon'  => 'fa-th-large',
                'child' => [
                    [
                        'id'    => 210301,
                        'title' => '更新菜单',
                        'sort'  => 1,
                        'url'   => 'menu/import_menu',
                        'hide'  => 1,
                    ],
                ],
            ],
            [
                'id'    => 210400,
                'title' => '规则设置',
                'sort'  => 4,
                'url'   => 'rule/index',
                'hide'  => 1,
                'icon'  => 'fa-th-large',
                'child' => [
                    [
                        'id'    => 210401,
                        'title' => '玩法规则',
                        'sort'  => 1,
                        'url'   => 'rule/setting',
                        'hide'  => 1,
                    ],
                ],
            ],
        ],
     ],

    'statistics' => [
        'id'    => 80000,
        'title' => '统计中心',
        'sort'  => 9,
        'url'   => 'statistics/index',
        'hide'  => 1,
        'icon'  => 'glyphicon glyphicon-wrench',
        'child' => [
            [
                'id'    => 80100,
                'title' => '会员分析',
                'sort'  => 1,
                'url'   => '',
                'hide'  => 1,
                'icon'  => 'fa-th-large',
                'child' => [
                    [
                        'id'    => 80101,
                        'title' => '会员消费排行',
                        'sort'  => 1,
                        'url'   => '',
                        'hide'  => 1,
                    ],
                    [
                        'id'    => 80102,
                        'title' => '会员增长趋势',
                        'sort'  => 2,
                        'url'   => '',
                        'hide'  => 1,
                    ],
                    [
                        'id'    => 80103,
                        'title' => '分销商增长趋势统计',
                        'sort'  => 3,
                        'url'   => '',
                        'hide'  => 1,
                    ],
                    [
                        'id'    => 80104,
                        'title' => '会员积分统计',
                        'sort'  => 4,
                        'url'   => '',
                        'hide'  => 1,
                    ],
                    [
                        'id'    => 80105,
                        'title' => '会员余额统计',
                        'sort'  => 5,
                        'url'   => '',
                        'hide'  => 1,
                    ],
                    [
                        'id'    => 80106,
                        'title' => '会员现金消费统计',
                        'sort'  => 6,
                        'url'   => '',
                        'hide'  => 1,
                    ],
                    [
                        'id'    => 80107,
                        'title' => '用户分析',
                        'sort'  => 6,
                        'url'   => '',
                        'hide'  => 1,
                    ],
                ],
            ],
            [
                'id'    => 80200,
                'title' => '销售分析',
                'sort'  => 1,
                'url'   => '',
                'hide'  => 1,
                'icon'  => 'fa-th-large',
                'child' => [
                    [
                        'id'    => 80201,
                        'title' => '销售统计',
                        'sort'  => 1,
                        'url'   => '',
                        'hide'  => 1,
                    ],
                    [
                        'id'    => 80203,
                        'title' => '销售指标',
                        'sort'  => 2,
                        'url'   => '',
                        'hide'  => 1,
                    ],
                    [
                        'id'    => 80204,
                        'title' => '订单统计',
                        'sort'  => 3,
                        'url'   => '',
                        'hide'  => 1,
                    ],
                    [
                        'id'    => 80205,
                        'title' => '流量入口统计',
                        'sort'  => 4,
                        'url'   => 'distribution/distribution_grade',
                        'hide'  => 1,
                    ],
                ],
            ],
            [
                'id'    => 80300,
                'title' => '商品分析',
                'sort'  => 1,
                'url'   => '',
                'hide'  => 1,
                'icon'  => 'fa-th-large',
                'child' => [
                    [
                        'id'    => 80301,
                        'title' => '商品销售明细',
                        'sort'  => 1,
                        'url'   => '',
                        'hide'  => 1,
                    ],
                    [
                        'id'    => 80302,
                        'title' => '商品销售排行',
                        'sort'  => 2,
                        'url'   => '',
                        'hide'  => 1,
                    ],
                    [
                        'id'    => 80303,
                        'title' => '商品销售转化率',
                        'sort'  => 3,
                        'url'   => '',
                        'hide'  => 1,
                    ],
                    [
                        'id'    => 80304,
                        'title' => '入驻商家销售排行',
                        'sort'  => 4,
                        'url'   => 'distribution/distribution_grade',
                        'hide'  => 1,
                    ],
                    [
                        'id'    => 80305,
                        'title' => '入驻商家流量排行',
                        'sort'  => 4,
                        'url'   => 'distribution/distribution_grade',
                        'hide'  => 1,
                    ],
                ],
            ],
        ],
    ],

];
