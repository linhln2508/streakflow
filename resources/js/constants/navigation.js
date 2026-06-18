import { normalizePath, pathMatches } from '@/constants/layout';

export const mainNavItems = (isAdmin = false, currentUrl = '', unclosedDaysCount = 0) => {
    const items = [
        {
            label: 'Hôm nay',
            href: route('dashboard'),
            active: pathMatches(currentUrl, route('dashboard')),
            icon: 'CalendarCheck',
            badge: unclosedDaysCount > 0 ? unclosedDaysCount : null,
        },
        {
            label: 'Tasks',
            href: route('tasks.index'),
            active: pathMatches(currentUrl, route('tasks.index')),
            icon: 'CheckCircle2',
        },
        {
            label: 'Danh mục',
            href: route('categories.index'),
            active: pathMatches(currentUrl, route('categories.index')),
            icon: 'Briefcase',
        },
        {
            label: 'Báo cáo',
            href: route('reports.overview'),
            active: normalizePath(currentUrl).startsWith('/reports'),
            icon: 'Trophy',
        },
    ];

    if (isAdmin) {
        items.push({
            label: 'Admin',
            href: route('admin.users'),
            active: normalizePath(currentUrl).startsWith('/admin'),
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
