interface CalendarDay {
    day_number: number;
    unlocked_at: string | null;
}

/**
 * Find the next day that should show a countdown (first unlocked day)
 */
export function getNextDayForCountdown(days: CalendarDay[]): number | null {
    // Sort days by day_number and find the first unlocked day
    const sortedDays = [...days].sort((a, b) => a.day_number - b.day_number);
    const nextDay = sortedDays.find(day => !day.unlocked_at);
    return nextDay ? nextDay.day_number : null;
}

/**
 * Calculate countdown string for a specific day
 * Returns null if the day can already be unlocked or if countdown is not needed
 */
export function getCountdownString(
    dayNumber: number,
    isUnlocked: boolean,
    year: number,
    currentTime: Date
): string | null {
    // If already unlocked, no countdown needed
    if (isUnlocked) {
        return null;
    }

    const currentDay = currentTime.getDate();
    const currentMonth = currentTime.getMonth() + 1; // getMonth() returns 0-11
    const currentHour = currentTime.getHours();
    const currentMinute = currentTime.getMinutes();
    const currentSecond = currentTime.getSeconds();

    // Target time is always 07:00:00
    const targetHour = 7;
    const targetMinute = 0;
    const targetSecond = 0;

    let targetDate: Date;

    if (currentMonth !== 12) {
        // Not December yet - countdown to December 1st at 07:00
        targetDate = new Date(year, 11, 1, targetHour, targetMinute, targetSecond); // Month is 0-indexed, so 11 = December
    } else if (dayNumber > currentDay) {
        // December, but day hasn't arrived yet - countdown to that day at 07:00
        targetDate = new Date(year, 11, dayNumber, targetHour, targetMinute, targetSecond);
    } else if (dayNumber === currentDay) {
        // It's the current day - check if it's past 07:00
        if (currentHour >= targetHour) {
            // Already past 07:00, can unlock now - no countdown needed
            return null;
        }
        // Before 07:00 - countdown to 07:00 today
        targetDate = new Date(year, 11, dayNumber, targetHour, targetMinute, targetSecond);
    } else {
        // Past day that hasn't been unlocked - should be unlockable now, no countdown
        return null;
    }

    // Calculate time difference
    const diff = targetDate.getTime() - currentTime.getTime();

    // If target is in the past, return null (shouldn't happen, but safety check)
    if (diff <= 0) {
        return null;
    }

    // Calculate days, hours, minutes, seconds
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    // Format as DD:HH:MM:SS or HH:MM:SS
    if (days > 0) {
        return `${String(days).padStart(2, '0')}:${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    } else {
        return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }
}
