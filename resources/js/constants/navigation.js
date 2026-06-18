export const mainNavItems = (isAdmin = false) => {
    const items = [
        {
            label: 'Hôm nay',
            href: route('dashboard'),
            active: route().current('dashboard'),
            icon: 'CalendarCheck',
        },
        {
            label: 'Tasks',
            href: route('tasks.index'),
            active: route().current('tasks.*'),
            icon: 'CheckCircle2',
        },
        {
            label: 'Danh mục',
            href: route('categories.index'),
            active: route().current('categories.*'),
            icon: 'Briefcase',
        },
        {
            label: 'Báo cáo',
            href: route('reports.overview'),
            active: route().current('reports.*'),
            icon: 'Trophy',
        },
    ];

    if (isAdmin) {
        items.push({
            label: 'Admin',
            href: route('admin.users'),
            active: route().current('admin.*'),
            icon: 'Shield',
        });
    }

    return items;
};

export const authHighlights = [
    { icon: 'CalendarCheck', text: 'Task & thói quen — tự động mỗi ngày' },
    { icon: 'Heart', text: 'Gamification: HP, streak, XP, level' },
    { icon: 'Sparkles', text: 'Mở rộng thêm tính năng linh tinh' },
];

export const demoAccounts = [
    { label: 'User demo', email: 'user@linhtinh.test' },
    { label: 'Admin demo', email: 'admin@linhtinh.test' },
];
