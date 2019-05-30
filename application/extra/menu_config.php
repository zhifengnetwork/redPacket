<?php
return [
    # 会员管理
    'users' => [
        'id'    => 10000,
        'title' => '平台管理',
        'sort'  => 21,
        'url'   => 'users/index',
        'hide'  => 1,
        'icon'  => 'glyphicon glyphicon-user',
        'child' => [

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
                    [
                        'id'    => 210402,
                        'title' => '规则海报',
                        'sort'  => 1,
                        'url'   => 'rule/haibao',
                        'hide'  => 1,
                    ],                    
                ],
            ],
            [
                'id'    => 12000,
                'title' => '财务管理',
                'sort'  => 5,
                'url'   => '',
                'hide'  => 1,
                'icon'  => 'fa-th-large',
                'child' => [
                    [
                        'id'    => 12001,
                        'title' => '余额记录',
                        'sort'  => 1,
                        'url'   => 'users/account_log',
                        'hide'  => 1,
                    ],
                    [
                        'id'    => 12002,
                        'title' => '待充值',
                        'sort'  => 1,
                        'url'   => 'users/ck_recharge_list',
                        'hide'  => 1,
                    ],
                    [
                        'id'    => 12003,
                        'title' => '收款码管理',
                        'sort'  => 1,
                        'url'   => 'users/receipt_code ',
                        'hide'  => 1,
                    ],
                    [
                        'id'    => 12004,
                        'title' => '待提现',
                        'sort'  => 1,
                        'url'   => 'users/tx_list ',
                        'hide'  => 1,
                    ],
                ],
            ],















            [
                'id'    => 11000,
                'title' => '用户管理',
                'sort'  => 1,
                'url'   => 'users/index',
                'hide'  => 1,
                'icon'  => 'fa-th-large',
                'child' => [
                    [
                        'id'    => 11100,
                        'title' => '用户列表',
                        'sort'  => 1,
                        'url'   => 'users/index',
                        'hide'  => 1,
                        'icon'  => 'fa-th-large',
                    ],
                ],
            ],
            [
                'id'    => 11001,
                'title' => '群组管理',
                'sort'  => 1,
                'url'   => 'users/groupList',
                'hide'  => 1,
                'icon'  => 'fa-th-large',
                'child' => [
                    [
                        'id'    => 11101,
                        'title' => '群组列表',
                        'sort'  => 1,
                        'url'   => 'users/groupList',
                        'hide'  => 1,
                        'icon'  => 'fa-th-large',
                    ],
                    
                ],
            ],
            [
                'id'    => 11002,
                'title' => '客服管理',
                'sort'  => 1,
                'url'   => 'users/serviceList',
                'hide'  => 1,
                'icon'  => 'fa-th-large',
                'child' => [
                    [
                        'id'    => 11102,
                        'title' => '客服列表',
                        'sort'  => 1,
                        'url'   => 'users/serviceList',
                        'hide'  => 1,
                        'icon'  => 'fa-th-large',
                    ],
                    
                ],
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
            
        ],
    ],

];
