<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Hướng dẫn & Luật chơi" />

    <PageHeader
        title="Hướng dẫn & Luật chơi"
        description="Cách dùng Linh Tinh — task, chốt ngày và gamification"
        :breadcrumbs="[{ label: 'Hướng dẫn' }]"
    />

    <PageContainer class="space-y-8 pb-12">
        <PageSection title="Quy trình hàng ngày">
            <ol class="list-decimal space-y-2 pl-5 text-sm text-muted-foreground">
                <li>Mở <Link :href="route('dashboard')" class="font-medium text-primary hover:underline">Hôm nay</Link> — xử lý task (Done / Skip).</li>
                <li>Chốt từng ngày có task (kể cả ngày cũ chưa chốt) qua thanh chọn ngày.</li>
                <li>Sau khi chốt: nhận HP, XP; streak và shield cập nhật theo % hoàn thành.</li>
            </ol>
        </PageSection>

        <PageSection title="HP (0–100)">
            <ul class="space-y-2 text-sm text-muted-foreground">
                <li>• Mỗi ngày có <strong>quota skip miễn phí = 25%</strong> tổng task (làm tròn xuống).</li>
                <li>• Skip trong quota → cộng HP (saved skip).</li>
                <li>• Skip vượt quota → trừ HP (over skip).</li>
                <li>• Task pending khi chốt ngày được tính như skip tự động.</li>
            </ul>
        </PageSection>

        <PageSection title="XP & Level">
            <ul class="space-y-2 text-sm text-muted-foreground">
                <li>• XP = base (theo số task) × hệ số hiệu suất × bonus streak.</li>
                <li>• XP không bao giờ bị trừ.</li>
                <li>• Level tăng theo tổng XP tích lũy (công thức trong README).</li>
            </ul>
        </PageSection>

        <PageSection id="streak" title="Streak">
            <ul class="space-y-2 text-sm text-muted-foreground">
                <li>• <strong>≥75%</strong> hoàn thành khi chốt ngày → streak +1.</li>
                <li>• <strong>100%</strong> → thêm 1 Shield và xóa nợ shield (nếu có).</li>
                <li>• <strong>&lt;75%</strong> → streak có thể mất — xem Shield bên dưới.</li>
            </ul>
        </PageSection>

        <PageSection id="shield" title="Shield & Ứng trước">
            <div class="space-y-4 text-sm text-muted-foreground">
                <p>
                    <strong class="text-foreground">Shield</strong> bảo vệ streak khi bạn chốt ngày dưới 75%.
                    Kiếm shield bằng cách hoàn thành 100% task trong ngày.
                </p>
                <p>
                    Khi chốt ngày &lt;75%, hệ thống hỏi bạn chọn:
                </p>
                <ul class="space-y-2 pl-4">
                    <li>• <strong>Dùng Shield</strong> — trừ 1 shield, giữ streak.</li>
                    <li>• <strong>Ứng trước (nợ shield)</strong> — chỉ khi streak ≥30 và chưa có nợ: giữ streak lần này nhưng ghi 1 nợ. Lần sau &lt;75% mà không có shield → mất streak.</li>
                    <li>• <strong>Chấp nhận mất streak</strong> — streak về 0.</li>
                </ul>
                <p>
                    Nếu streak &lt;30 và không có shield: chỉ có thể chấp nhận mất streak.
                </p>
            </div>
        </PageSection>

        <PageSection title="Skip & Task">
            <ul class="space-y-2 text-sm text-muted-foreground">
                <li>• Skip chủ động trước khi chốt — tính vào quota skip.</li>
                <li>• Task template lặp lại tự sinh instance mỗi ngày (cron 00:05).</li>
                <li>• Task nhanh chỉ tạo trên ngày hôm nay.</li>
            </ul>
        </PageSection>

        <div class="text-center">
            <Button as="a" :href="route('dashboard')" variant="default" size="pill">Về Hôm nay</Button>
        </div>
    </PageContainer>
</template>
