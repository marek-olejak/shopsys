import { SpinnerIcon } from 'components/Basic/Icon/SpinnerIcon';
import { TIDs } from 'cypress/tids';

export const LoaderWithOverlay: FC = ({ className }) => (
    <div
        className="absolute inset-0 z-overlay flex h-full w-full items-center justify-center bg-overlay"
        tid={TIDs.loader_overlay}
    >
        <SpinnerIcon className={className} />
    </div>
);
