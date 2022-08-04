import React, {createContext, FC, useCallback} from 'react';
import {useStorageState} from '@akeneo-pim-community/shared';

type Configuration = {
  features: {
    permission: boolean;
    enriched_category: boolean;
  };
  acls: {
    pim_enrich_product_categories_view: boolean;
    pim_enrich_product_category_create: boolean;
    pim_enrich_product_category_edit: boolean;
    pim_enrich_product_category_history: boolean;
    pim_enrich_product_category_list: boolean;
    pim_enrich_product_category_remove: boolean;
    pimee_enrich_category_edit_permissions: boolean;
  };
};

type WriteConfiguration = {
  features?: {
    permission?: boolean;
    enriched_category?: boolean;
  };
  acls?: {
    pim_enrich_product_categories_view?: boolean;
    pim_enrich_product_category_create?: boolean;
    pim_enrich_product_category_edit?: boolean;
    pim_enrich_product_category_history?: boolean;
    pim_enrich_product_category_list?: boolean;
    pim_enrich_product_category_remove?: boolean;
    pimee_enrich_category_edit_permissions?: boolean;
  };
};

type ConfigurationState = {
  configuration: Configuration;
  setDefaultCommunitySettings: () => void;
  setDefaultGrowthSettings: () => void;
  setDefaultEnterpriseSettings: () => void;
  updateConfiguration: (config: WriteConfiguration) => void;
};

const CONFIGURATION_STORAGE_KEY = 'CategoryMicroFrontendConfiguration';

const DEFAULT_CONFIGURATION: Configuration = {
  features: {
    permission: true,
    enriched_category: true,
  },
  acls: {
    pim_enrich_product_categories_view: true,
    pim_enrich_product_category_create: true,
    pim_enrich_product_category_edit: true,
    pim_enrich_product_category_history: true,
    pim_enrich_product_category_list: true,
    pim_enrich_product_category_remove: true,
    pimee_enrich_category_edit_permissions: true,
  },
};

const ConfigurationContext = createContext<ConfigurationState | undefined>(undefined);

const ConfigurationProvider: FC = ({children}) => {
  const [configuration, setConfiguration] = useStorageState(DEFAULT_CONFIGURATION, CONFIGURATION_STORAGE_KEY);

  const setDefaultCommunitySettings = useCallback(() => {
    setConfiguration({
      features: {
        permission: false,
        enriched_category: false,
      },
      acls: {
        pim_enrich_product_categories_view: true,
        pim_enrich_product_category_create: true,
        pim_enrich_product_category_edit: true,
        pim_enrich_product_category_history: true,
        pim_enrich_product_category_list: true,
        pim_enrich_product_category_remove: true,
        pimee_enrich_category_edit_permissions: false,
      },
    });
  }, [setConfiguration]);

  const setDefaultGrowthSettings = useCallback(() => {
    setConfiguration({
      features: {
        permission: false,
        enriched_category: true,
      },
      acls: {
        pim_enrich_product_categories_view: true,
        pim_enrich_product_category_create: true,
        pim_enrich_product_category_edit: true,
        pim_enrich_product_category_history: true,
        pim_enrich_product_category_list: true,
        pim_enrich_product_category_remove: true,
        pimee_enrich_category_edit_permissions: false,
      },
    });
  }, [setConfiguration]);

  const setDefaultEnterpriseSettings = useCallback(() => {
    setConfiguration({
      features: {
        permission: true,
        enriched_category: true,
      },
      acls: {
        pim_enrich_product_categories_view: true,
        pim_enrich_product_category_create: true,
        pim_enrich_product_category_edit: true,
        pim_enrich_product_category_history: true,
        pim_enrich_product_category_list: true,
        pim_enrich_product_category_remove: true,
        pimee_enrich_category_edit_permissions: true,
      },
    });
  }, [setConfiguration]);

  // @todo split updateConfiguration in setFeature and setAcl
  const updateConfiguration = useCallback(
    (config: WriteConfiguration) => {
      setConfiguration(configuration => {
        return {
          features: {
            ...configuration.features,
            ...(config.features ?? {}),
          },
          acls: {
            ...configuration.acls,
            ...(config.acls ?? {}),
          },
        };
      });
    },
    [setConfiguration]
  );

  const state = {
    configuration,
    updateConfiguration,
    setDefaultCommunitySettings,
    setDefaultGrowthSettings,
    setDefaultEnterpriseSettings,
  };

  return <ConfigurationContext.Provider value={state}>{children}</ConfigurationContext.Provider>;
};
export type {ConfigurationState, Configuration, WriteConfiguration};
export {ConfigurationContext, ConfigurationProvider};
