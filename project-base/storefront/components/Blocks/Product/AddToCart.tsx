import { Loader } from 'components/Basic/Loader/Loader';
import { Button } from 'components/Forms/Button/Button';
import { Spinbox } from 'components/Forms/Spinbox/Spinbox';
import { TIDs } from 'cypress/tids';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useRef } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { useAddToCart } from 'utils/cart/useAddToCart';
import { twMergeCustom } from 'utils/twMerge';

const AddToCartPopup = dynamic(() =>
    import('components/Blocks/Popup/AddToCartPopup').then((component) => component.AddToCartPopup),
);

type AddToCartProps = {
    productUuid: string;
    minQuantity: number;
    maxQuantity: number;
    gtmMessageOrigin: GtmMessageOriginType;
    gtmProductListName: GtmProductListNameType;
    listIndex: number;
    isWithSpinbox?: boolean;
};

export const AddToCart: FC<AddToCartProps> = ({
    productUuid,
    minQuantity,
    maxQuantity,
    gtmMessageOrigin,
    gtmProductListName,
    listIndex,
    className,
    isWithSpinbox = true,
}) => {
    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const { t } = useTranslation();
    const { addToCart, isAddingToCart } = useAddToCart(gtmMessageOrigin, gtmProductListName);
    const updatePortalContent = useSessionStore((s) => s.updatePortalContent);

    const onAddToCartHandler = async () => {
        if (isWithSpinbox && spinboxRef.current === null) {
            return;
        }

        const addedQuantity = isWithSpinbox ? spinboxRef.current!.valueAsNumber : 1;
        const addToCartResult = await addToCart(productUuid, addedQuantity, listIndex);

        if (isWithSpinbox) {
            spinboxRef.current!.valueAsNumber = 1;
        }

        if (addToCartResult) {
            updatePortalContent(
                <AddToCartPopup
                    key={addToCartResult.addProductResult.cartItem.uuid}
                    addedCartItem={addToCartResult.addProductResult.cartItem}
                />,
            );
        }
    };

    return (
        <div className={twMergeCustom('flex items-stretch justify-between gap-2', className)}>
            {isWithSpinbox && (
                <Spinbox
                    defaultValue={1}
                    id={productUuid}
                    max={maxQuantity}
                    min={minQuantity}
                    ref={spinboxRef}
                    size="small"
                    step={1}
                />
            )}
            <Button
                className="py-2"
                isDisabled={isAddingToCart}
                name="add-to-cart"
                size="small"
                tid={TIDs.blocks_product_addtocart}
                onClick={onAddToCartHandler}
            >
                <span>{t('Add to cart')}</span>
                {isAddingToCart && <Loader className="w-4" />}
            </Button>
        </div>
    );
};
