import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderDetailFragment } from '../fragments/OrderDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
export type TypeOrderDetailQueryVariables = Types.Exact<{
  orderNumber: Types.InputMaybe<Types.Scalars['String']['input']>;
}>;


export type TypeOrderDetailQuery = { __typename?: 'Query', order: { __typename: 'Order', uuid: string, number: string, creationDate: any, status: string, firstName: string | null, lastName: string | null, email: string, telephone: string, companyName: string | null, companyNumber: string | null, companyTaxNumber: string | null, street: string, city: string, postcode: string, isDeliveryAddressDifferentFromBilling: boolean, deliveryFirstName: string | null, deliveryLastName: string | null, deliveryCompanyName: string | null, deliveryTelephone: string | null, deliveryStreet: string | null, deliveryCity: string | null, deliveryPostcode: string | null, note: string | null, urlHash: string, promoCode: string | null, trackingNumber: string | null, trackingUrl: string | null, paymentTransactionsCount: number, isPaid: boolean, items: Array<{ __typename: 'OrderItem', name: string, vatRate: string, quantity: number, unit: string | null, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }>, transport: { __typename: 'Transport', name: string, isPersonalPickup: boolean, transportType: { __typename?: 'TransportType', code: string } }, payment: { __typename: 'Payment', name: string, type: string }, country: { __typename: 'Country', name: string }, deliveryCountry: { __typename: 'Country', name: string } | null, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } } | null };


      export interface PossibleTypesResultData {
        possibleTypes: {
          [key: string]: string[]
        }
      }
      const result: PossibleTypesResultData = {
  "possibleTypes": {
    "Advert": [
      "AdvertCode",
      "AdvertImage"
    ],
    "ArticleInterface": [
      "ArticleSite",
      "BlogArticle"
    ],
    "Breadcrumb": [
      "ArticleSite",
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Category",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "Store",
      "Variant"
    ],
    "CustomerUser": [
      "CompanyCustomerUser",
      "RegularCustomerUser"
    ],
    "Hreflang": [
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "SeoPage",
      "Variant"
    ],
    "NotBlogArticleInterface": [
      "ArticleLink",
      "ArticleSite"
    ],
    "ParameterFilterOptionInterface": [
      "ParameterCheckboxFilterOption",
      "ParameterColorFilterOption",
      "ParameterSliderFilterOption"
    ],
    "Product": [
      "MainVariant",
      "RegularProduct",
      "Variant"
    ],
    "ProductListable": [
      "Brand",
      "Category",
      "Flag"
    ],
    "Slug": [
      "ArticleSite",
      "BlogArticle",
      "BlogCategory",
      "Brand",
      "Category",
      "Flag",
      "MainVariant",
      "RegularProduct",
      "Store",
      "Variant"
    ]
  }
};
      export default result;
    

export const OrderDetailQueryDocument = gql`
    query OrderDetailQuery($orderNumber: String) {
  order(orderNumber: $orderNumber) {
    ...OrderDetailFragment
  }
}
    ${OrderDetailFragment}`;

export function useOrderDetailQuery(options?: Omit<Urql.UseQueryArgs<TypeOrderDetailQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeOrderDetailQuery, TypeOrderDetailQueryVariables>({ query: OrderDetailQueryDocument, ...options });
};