import { useRegistrationMutation } from 'graphql/requests/registration/mutations/RegistrationMutation.generated';
import { TypeRegistrationDataInput } from 'graphql/types';
import { GtmFormType } from 'gtm/enums/GtmFormType';
import { onGtmSendFormEventHandler } from 'gtm/handlers/onGtmSendFormEventHandler';
import { useRouter } from 'next/router';
import { usePersistStore } from 'store/usePersistStore';
import { useSessionStore } from 'store/useSessionStore';
import { setTokensToCookies } from 'utils/auth/setTokensToCookies';
import { blurInput } from 'utils/forms/blurInput';

export const useRegistration = () => {
    const [, registerMutation] = useRegistrationMutation();
    const router = useRouter();
    const updateAuthLoadingState = usePersistStore((s) => s.updateAuthLoadingState);
    const updatePageLoadingState = useSessionStore((s) => s.updatePageLoadingState);
    const updateUserEntryState = usePersistStore((s) => s.updateUserEntryState);
    const updateCartUuid = usePersistStore((store) => store.updateCartUuid);
    const productListUuids = usePersistStore((s) => s.productListUuids);
    const updateProductListUuids = usePersistStore((s) => s.updateProductListUuids);

    const register = async (registrationInput: Omit<TypeRegistrationDataInput, 'productListsUuids'>) => {
        blurInput();
        const registerResult = await registerMutation({
            input: {
                cartUuid: registrationInput.cartUuid,
                city: registrationInput.city,
                companyCustomer: registrationInput.companyCustomer,
                companyName: registrationInput.companyName,
                companyNumber: registrationInput.companyNumber,
                companyTaxNumber: registrationInput.companyTaxNumber,
                country: registrationInput.country,
                email: registrationInput.email,
                firstName: registrationInput.firstName,
                lastName: registrationInput.lastName,
                lastOrderUuid: registrationInput.lastOrderUuid,
                newsletterSubscription: registrationInput.newsletterSubscription,
                password: registrationInput.password,
                postcode: registrationInput.postcode,
                street: registrationInput.street,
                telephone: registrationInput.telephone,
                productListsUuids: Object.values(productListUuids),
                billingAddressUuid: null,
            },
        });

        if (registerResult.data?.Register) {
            const accessToken = registerResult.data.Register.tokens.accessToken;
            const refreshToken = registerResult.data.Register.tokens.refreshToken;

            setTokensToCookies(accessToken, refreshToken);
            updateCartUuid(null);
            updateProductListUuids({});

            updateAuthLoadingState(
                registerResult.data.Register.showCartMergeInfo
                    ? 'registration-loading-with-cart-modifications'
                    : 'registration-loading',
            );
            updatePageLoadingState({ isPageLoading: true, redirectPageType: 'homepage' });
            updateUserEntryState('registration');
            onGtmSendFormEventHandler(GtmFormType.registration);
            router.replace('/').then(() => router.reload());

            return undefined;
        }

        return registerResult.error;
    };

    return register;
};
