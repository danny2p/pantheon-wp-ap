import React from 'react';
import { StyleContainer } from '@divi/module';

export const ModuleStyles = ({
  settings,
  mode,
  state,
  noStyleTag,
  elements,
}: any) => (
  <StyleContainer mode={mode} state={state} noStyleTag={noStyleTag}>
    {elements.style({
      attrName: 'module',
      styleProps: {
        disabledOn: {
          disabledModuleVisibility: settings?.disabledModuleVisibility,
        },
      },
    })}
  </StyleContainer>
);
