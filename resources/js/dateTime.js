function getDateTimeParts(dateTime, timeZone) {
    const formattedParts = new Intl.DateTimeFormat('en-US', {
        timeZone,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        hourCycle: 'h23',
    }).formatToParts(new Date(dateTime));

    return Object.fromEntries(
        formattedParts
            .filter(({ type }) => type !== 'literal')
            .map(({ type, value }) => [type, value]),
    );
}

export function formatLocalDate(dateTime, timeZone) {
    const { year, month, day } = getDateTimeParts(dateTime, timeZone);

    return `${year}-${month}-${day}`;
}

export function formatDateTimeInput(dateTime, timeZone) {
    const { year, month, day, hour, minute } = getDateTimeParts(dateTime, timeZone);

    return `${year}-${month}-${day}T${hour}:${minute}`;
}

export function formatLocalTime(dateTime, timeZone) {
    return new Intl.DateTimeFormat('en-US', {
        timeZone,
        hour: 'numeric',
        minute: '2-digit',
    }).format(new Date(dateTime));
}

export function calculateDurationMinutes(startedAt, endedAt) {
    if (!endedAt) {
        return null;
    }

    return Math.floor((new Date(endedAt).getTime() - new Date(startedAt).getTime()) / 60000);
}

export function formatLocalDayLabel(localDate, timeZone) {
    const today = formatLocalDate(new Date(), timeZone);
    const [year, month, day] = today.split('-').map(Number);
    const yesterday = new Date(Date.UTC(year, month - 1, day - 1)).toISOString().slice(0, 10);

    if (localDate === today) {
        return 'Today';
    }

    if (localDate === yesterday) {
        return 'Yesterday';
    }

    return new Intl.DateTimeFormat('en-US', {
        timeZone: 'UTC',
        weekday: 'short',
        month: 'short',
        day: 'numeric',
    }).format(new Date(`${localDate}T12:00:00Z`));
}
