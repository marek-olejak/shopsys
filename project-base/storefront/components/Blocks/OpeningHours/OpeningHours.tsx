import { TypeOpeningHours } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { Fragment } from 'react';
import { twJoin } from 'tailwind-merge';
import { useFormatDate } from 'utils/formatting/useFormatDate';
import { StoreOrPacketeryPoint } from 'utils/packetery/types';
import { twMergeCustom } from 'utils/twMerge';

export const OpeningHours: FC<{ openingHours: StoreOrPacketeryPoint['openingHours'] | TypeOpeningHours }> = ({
    openingHours,
    className,
}) => {
    const { t } = useTranslation();
    const { formatDate } = useFormatDate();

    const getDayName = (currentDayOfWeek: number, requestedDayOfWeek: number): string => {
        const dayNames = [
            t('Monday'),
            t('Tuesday'),
            t('Wednesday'),
            t('Thursday'),
            t('Friday'),
            t('Saturday'),
            t('Sunday'),
        ];

        const dayName = dayNames[requestedDayOfWeek - 1];

        switch (requestedDayOfWeek - currentDayOfWeek) {
            case 0:
                return t('Today');
            case 1:
                return t('Tomorrow');
            default:
                return dayName;
        }
    };

    if (openingHours.openingHoursOfDays.length === 0) {
        return null;
    }

    return (
        <>
            <div className="my-1 text-textDisabled">{t('Open') + ': '}</div>
            {'exceptionDays' in openingHours &&
                openingHours.exceptionDays?.map((exceptionDay, index) => {
                    let exceptionDayText = formatDate(exceptionDay.from, 'D.M.');

                    if (exceptionDay.to) {
                        exceptionDayText += ` - ${formatDate(exceptionDay.to, 'D.M.')}`;
                    }

                    if (exceptionDay.times.length) {
                        for (let index = 0; index < exceptionDay.times.length; index++) {
                            if (index === 0) {
                                exceptionDayText += ` ${exceptionDay.times[index].open} - ${exceptionDay.times[index].close}`;
                            } else {
                                exceptionDayText += `, ${exceptionDay.times[index].open} - ${exceptionDay.times[index].close}`;
                            }
                        }
                    } else {
                        exceptionDayText += ` ${t('Closed')}`;
                    }

                    return (
                        <div
                            key={index}
                            className={twMergeCustom('flex w-full flex-col text-sm text-textError', className)}
                        >
                            {exceptionDayText}
                        </div>
                    );
                })}

            <div className={twMergeCustom('flex w-full flex-col text-sm', className)}>
                {openingHours.openingHoursOfDays.map(({ date, dayOfWeek, openingHoursRanges }) => {
                    const isToday = openingHours.dayOfWeek === dayOfWeek;
                    const isClosedWholeDay = openingHoursRanges.length === 0;

                    return (
                        <div
                            key={dayOfWeek}
                            className={twJoin(
                                'flex flex-row',
                                isToday && openingHours.isOpen && 'text-openingStatusOpen',
                                isToday && !openingHours.isOpen && 'text-openingStatusOpenToday',
                                isToday && isClosedWholeDay && 'text-openingStatusClosed',
                            )}
                        >
                            <strong className="basis-32 text-left">
                                {getDayName(openingHours.dayOfWeek, dayOfWeek)} {formatDate(date, 'D.M.')}
                            </strong>
                            <span className="flex-1">
                                {isClosedWholeDay ? (
                                    <>&nbsp;{t('Closed')}</>
                                ) : (
                                    openingHoursRanges.map(({ openingTime, closingTime }, index) => (
                                        <Fragment key={index}>
                                            {index > 0 && ','} {openingTime}&nbsp;-&nbsp;{closingTime}
                                        </Fragment>
                                    ))
                                )}
                            </span>
                        </div>
                    );
                })}
            </div>
        </>
    );
};
