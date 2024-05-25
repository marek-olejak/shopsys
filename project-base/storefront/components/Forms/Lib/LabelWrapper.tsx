import { CheckmarkIcon } from 'components/Basic/Icon/CheckmarkIcon';
import { LabelHTMLAttributes, ReactNode } from 'react';
import { ExtractNativePropsFromDefault } from 'types/ExtractNativePropsFromDefault';
import { twMergeCustom } from 'utils/twMerge';

type NativeProps = ExtractNativePropsFromDefault<LabelHTMLAttributes<HTMLLabelElement>, never, 'htmlFor'>;

type LabelWrapperProps = NativeProps & {
    label: string | ReactNode | ReactNode[] | undefined;
    count?: number;
    inputType: 'textarea' | 'text-input' | 'checkbox' | 'radio' | 'selectbox';
    required?: boolean;
    checked?: boolean;
    disabled?: boolean;
    selectBoxLabelIsFloated?: boolean;
};

export const LabelWrapper: FC<LabelWrapperProps> = ({
    label,
    count,
    inputType,
    required,
    checked,
    disabled,
    selectBoxLabelIsFloated,
    htmlFor,
    children,
    className,
}) => (
    <div className="relative w-full select-none">
        {children}
        {!!label && (
            <label
                htmlFor={htmlFor}
                // "peer" here is input passed from parent component
                // see https://tailwindcss.com/docs/hover-focus-and-other-states#styling-based-on-sibling-state
                className={twMergeCustom(
                    inputType === 'text-input' &&
                        'top-2 text-xs peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-base peer-focus:top-2 peer-focus:text-sm',
                    (inputType === 'text-input' || inputType === 'selectbox' || inputType === 'textarea') &&
                        'absolute left-3 z-[2] block text-sm text-skyBlue transition-all',
                    (inputType === 'text-input' || inputType === 'selectbox' || inputType === 'textarea') &&
                        (selectBoxLabelIsFloated === undefined || selectBoxLabelIsFloated === true) &&
                        'transform-none peer-placeholder-shown:-translate-y-1/2 peer-focus:transform-none',
                    (inputType === 'checkbox' || inputType === 'radio') && [
                        'group relative flex w-full cursor-pointer items-center gap-2 text-base text-dark',
                        disabled && 'cursor-no-drop text-skyBlue opacity-60',
                    ],
                    inputType === 'checkbox' && [
                        '[&>a]:text-dark [&>a]:hover:text-secondary [&>a]:focus:text-secondary [&>a]:active:text-secondary',
                    ],
                    inputType === 'selectbox' && [
                        'top-1/2 -translate-y-1/2',
                        selectBoxLabelIsFloated && 'top-[9px] text-xs',
                    ],
                    inputType === 'textarea' &&
                        'top-2 text-xs peer-placeholder-shown:top-5 peer-placeholder-shown:text-base peer-focus:top-2 peer-focus:text-xs',
                    disabled && 'text-skyBlue',
                    className,
                )}
            >
                {(inputType === 'checkbox' || inputType === 'radio') && (
                    <div
                        className={twMergeCustom(
                            'flex w-5 h-5 min-w-5 border border-black bg-white p-[3px] text-white transition',
                            inputType === 'checkbox' ? 'rounded' : 'rounded-full p-[5px]',
                            'active:scale-90',
                            checked
                                ? 'border-primary bg-primary group-hover:bg-primaryDark group-hover:border-primaryDark'
                                : 'group-hover:border-primary group-active:border-primary group-hover:bg-whiteSnow',
                            disabled &&
                                'border-skyBlue outline-0 group-hover:border-skyBlue group-hover:bg-none group-active:border-skyBlue group-active:outline-0 active:scale-100',
                            disabled && checked && 'bg-skyBlue group-hover:bg-skyBlue',
                        )}
                    >
                        {inputType === 'checkbox' ? (
                            <CheckmarkIcon
                                className={twMergeCustom(
                                    'h-full opacity-0 transition',
                                    checked && 'opacity-100',
                                    disabled && 'text-skyBlue',
                                )}
                            />
                        ) : (
                            <span
                                className={twMergeCustom(
                                    'h-full w-full rounded-full bg-white opacity-0 transition',
                                    checked && 'opacity-100',
                                )}
                            />
                        )}
                    </div>
                )}

                <div className="w-full">
                    {label}
                    {!!count && !checked && ` (${count})`}
                    {required && <span className="ml-1 text-red">*</span>}
                </div>
            </label>
        )}
    </div>
);
