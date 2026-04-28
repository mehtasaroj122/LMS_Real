@props([
    'label' => 'Local Time',
    'variant' => 'inverse',
    'dateTime' => [],
])

@php
    $variantClasses = match ($variant) {
        'soft' => [
            'panel' => 'border border-slate-200/80 bg-white/80 shadow-sm shadow-slate-200/70',
            'label' => 'text-slate-500',
            'date' => 'text-slate-900',
            'time' => 'text-slate-950',
        ],
        default => [
            'panel' => 'border border-white/20 bg-white/10 shadow-lg shadow-slate-950/10',
            'label' => 'text-white/70',
            'date' => 'text-white',
            'time' => 'text-white',
        ],
    };

    $initialDate = data_get($dateTime, 'date', now('Asia/Kathmandu')->format('l, F j, Y'));
    $initialTime = data_get($dateTime, 'time', now('Asia/Kathmandu')->format('g:i:s A'));
@endphp

<div
    {{ $attributes->class([
        'min-w-[13.5rem] rounded-2xl px-4 py-3 backdrop-blur-md',
        $variantClasses['panel'],
    ]) }}
    data-dashboard-date-time-root
>
    <p class="text-[0.68rem] font-semibold uppercase tracking-[0.28em] {{ $variantClasses['label'] }}">
        {{ $label }}
    </p>
    <p class="mt-2 min-h-[1.5rem] text-sm font-semibold leading-6 {{ $variantClasses['date'] }}" data-dashboard-bs-date>
        {{ $initialDate }}
    </p>
    <p class="mt-1 text-xl font-black tracking-tight {{ $variantClasses['time'] }}" data-dashboard-time>
        {{ $initialTime }}
    </p>
</div>

@once
    <script>
        (() => {
            const BS_MONTHS = [
                'Baishakh',
                'Jestha',
                'Ashadh',
                'Shrawan',
                'Bhadra',
                'Ashwin',
                'Kartik',
                'Mangsir',
                'Poush',
                'Magh',
                'Falgun',
                'Chaitra',
            ];

            const BS_YEAR_DATA = [
                [2000, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31, 365],
                [2001, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2002, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30, 365],
                [2003, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31, 366],
                [2004, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31, 365],
                [2005, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2006, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30, 365],
                [2007, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31, 366],
                [2008, 31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 29, 31, 365],
                [2009, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2010, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30, 365],
                [2011, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31, 366],
                [2012, 31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 30, 30, 365],
                [2013, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2014, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30, 365],
                [2015, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31, 366],
                [2016, 31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 30, 30, 365],
                [2017, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2018, 31, 32, 31, 32, 31, 30, 30, 29, 30, 29, 30, 30, 365],
                [2019, 31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31, 366],
                [2020, 31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2021, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2022, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30, 365],
                [2023, 31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31, 366],
                [2024, 31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2025, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2026, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31, 366],
                [2027, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31, 365],
                [2028, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2029, 31, 31, 32, 31, 32, 30, 30, 29, 30, 29, 30, 30, 365],
                [2030, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31, 366],
                [2031, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31, 365],
                [2032, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2033, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30, 365],
                [2034, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31, 366],
                [2035, 30, 32, 31, 32, 31, 31, 29, 30, 30, 29, 29, 31, 365],
                [2036, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2037, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30, 365],
                [2038, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31, 366],
                [2039, 31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 30, 30, 365],
                [2040, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2041, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30, 365],
                [2042, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31, 366],
                [2043, 31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 30, 30, 365],
                [2044, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2045, 31, 32, 31, 32, 31, 30, 30, 29, 30, 29, 30, 30, 365],
                [2046, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31, 366],
                [2047, 31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2048, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2049, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30, 365],
                [2050, 31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31, 366],
                [2051, 31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2052, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2053, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30, 365],
                [2054, 31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31, 366],
                [2055, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2056, 31, 31, 32, 31, 32, 30, 30, 29, 30, 29, 30, 30, 365],
                [2057, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31, 366],
                [2058, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31, 365],
                [2059, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2060, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30, 365],
                [2061, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31, 366],
                [2062, 30, 32, 31, 32, 31, 31, 29, 30, 29, 30, 29, 31, 365],
                [2063, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2064, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30, 365],
                [2065, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31, 366],
                [2066, 31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 29, 31, 365],
                [2067, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2068, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30, 365],
                [2069, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31, 366],
                [2070, 31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 30, 30, 365],
                [2071, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2072, 31, 32, 31, 32, 31, 30, 30, 29, 30, 29, 30, 30, 365],
                [2073, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31, 366],
                [2074, 31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2075, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2076, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30, 365],
                [2077, 31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31, 366],
                [2078, 31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2079, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2080, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30, 365],
                [2081, 31, 31, 32, 32, 31, 30, 30, 30, 29, 30, 30, 30, 366],
                [2082, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30, 365],
                [2083, 31, 31, 32, 31, 31, 30, 30, 30, 29, 30, 30, 30, 365],
                [2084, 31, 31, 32, 31, 31, 30, 30, 30, 29, 30, 30, 30, 365],
                [2085, 31, 32, 31, 32, 30, 31, 30, 30, 29, 30, 30, 30, 366],
                [2086, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 30, 30, 365],
                [2087, 31, 31, 32, 31, 31, 31, 30, 30, 29, 30, 30, 30, 366],
                [2088, 30, 31, 32, 32, 30, 31, 30, 30, 29, 30, 30, 30, 365],
            ];

            const BS_EPOCH_UTC = Date.UTC(1943, 3, 14);

            const gregorianFallbackFormatter = new Intl.DateTimeFormat('en-US', {
                timeZone: 'Asia/Kathmandu',
                weekday: 'long',
                month: 'long',
                day: 'numeric',
                year: 'numeric',
            });

            const liveTimeFormatter = new Intl.DateTimeFormat('en-US', {
                timeZone: 'Asia/Kathmandu',
                hour: 'numeric',
                minute: '2-digit',
                second: '2-digit',
                hour12: true,
            });

            const kathmanduDateFormatter = new Intl.DateTimeFormat('en-US', {
                timeZone: 'Asia/Kathmandu',
                weekday: 'long',
                year: 'numeric',
                month: 'numeric',
                day: 'numeric',
            });

            const kathmanduHourFormatter = new Intl.DateTimeFormat('en-US', {
                timeZone: 'Asia/Kathmandu',
                hour: 'numeric',
                hourCycle: 'h23',
            });

            const getKathmanduParts = (date = new Date()) => {
                const parts = {};

                kathmanduDateFormatter.formatToParts(date).forEach((part) => {
                    if (part.type !== 'literal') {
                        parts[part.type] = part.value;
                    }
                });

                return {
                    weekday: parts.weekday ?? '',
                    year: Number(parts.year ?? 0),
                    month: Number(parts.month ?? 0),
                    day: Number(parts.day ?? 0),
                };
            };

            const convertGregorianDateToBs = (date = new Date()) => {
                const gregorian = getKathmanduParts(date);

                if (!gregorian.year || !gregorian.month || !gregorian.day) {
                    return null;
                }

                let remainingDays = Math.floor(
                    (Date.UTC(gregorian.year, gregorian.month - 1, gregorian.day) - BS_EPOCH_UTC) / 86400000
                );

                if (!Number.isFinite(remainingDays) || remainingDays < 0) {
                    return null;
                }

                for (let yearIndex = 0; yearIndex < BS_YEAR_DATA.length; yearIndex += 1) {
                    const yearData = BS_YEAR_DATA[yearIndex];
                    const yearLength = yearData[13];

                    if (remainingDays < yearLength) {
                        let monthIndex = 0;

                        while (monthIndex < 12 && remainingDays >= yearData[monthIndex + 1]) {
                            remainingDays -= yearData[monthIndex + 1];
                            monthIndex += 1;
                        }

                        return {
                            weekday: gregorian.weekday,
                            year: yearData[0],
                            monthIndex,
                            day: remainingDays + 1,
                        };
                    }

                    remainingDays -= yearLength;
                }

                return null;
            };

            const formatDashboardBsDate = (date = new Date()) => {
                const bsDate = convertGregorianDateToBs(date);

                if (!bsDate) {
                    return gregorianFallbackFormatter.format(date);
                }

                return `${bsDate.weekday}, ${BS_MONTHS[bsDate.monthIndex]} ${bsDate.day}, ${bsDate.year}`;
            };

            const formatDashboardTime = (date = new Date()) => liveTimeFormatter.format(date);

            const getDashboardGreeting = (date = new Date()) => {
                const currentHour = Number(kathmanduHourFormatter.format(date));

                if (currentHour < 12) {
                    return 'Good Morning';
                }

                if (currentHour < 17) {
                    return 'Good Afternoon';
                }

                return 'Good Evening';
            };

            const refreshDashboardDatePanels = (root = document) => {
                const currentDate = new Date();
                const greeting = getDashboardGreeting(currentDate);
                const bsDate = formatDashboardBsDate(currentDate);
                const time = formatDashboardTime(currentDate);

                root.querySelectorAll('[data-dashboard-greeting]').forEach((node) => {
                    node.textContent = greeting;
                });

                root.querySelectorAll('[data-dashboard-bs-date]').forEach((node) => {
                    node.textContent = bsDate;
                });

                root.querySelectorAll('[data-dashboard-time]').forEach((node) => {
                    node.textContent = time;
                });
            };

            window.DashboardDateTime = {
                getGreeting: getDashboardGreeting,
                formatBsDate: formatDashboardBsDate,
                formatTime: formatDashboardTime,
                refresh(root = document) {
                    refreshDashboardDatePanels(root);
                },
            };

            const initializeDashboardDatePanels = () => {
                refreshDashboardDatePanels(document);

                if (window.__dashboardDateTimePanelIntervalId) {
                    window.clearInterval(window.__dashboardDateTimePanelIntervalId);
                }

                window.__dashboardDateTimePanelIntervalId = window.setInterval(() => {
                    refreshDashboardDatePanels(document);
                }, 1000);
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeDashboardDatePanels, { once: true });
            } else {
                initializeDashboardDatePanels();
            }
        })();
    </script>
@endonce
