<?php
/** @var array $_ */
/** @var \OCP\IL10N $l */

$isCreationMode = isset($action) && $action === "create";

if($isCreationMode){
  $k = "{{provider_id}}";
}

?>

<div class="<?php p($provType) ?>-remove">x</div>
<?php foreach ($provData['fields'] as $fieldName => $fieldData): ?>
  <?php if($fieldData['type'] !== 'checkbox'): ?>
    <label class="sociallogin_fieldset">
        <?php p($l->t($fieldData['title'])) ?>
        <input
            type="<?php p($fieldData['type'])?>"
            name="<?php p($provType)?>_providers[<?php p($k) ?>][<?php p($fieldName)?>]"
            <?php if(!$isCreationMode): ?>
              value="<?php p($provider[$fieldName]) ?>"
            <?php
              p($fieldName === 'name' ? 'readonly' : '' );
            endif;
            $fieldData['required'] ? 'required' : '';?>
        />
    </label>
  <?php else:?>
    <div>
        <input type="checkbox" class="checkbox" value="1"
          id="<?php p($provType)?>_providers[<?php p($k) ?>][<?php p($fieldName)?>]"
          name="<?php p($provType)?>_providers[<?php p($k) ?>][<?php p($fieldName)?>]"
          value="1"
          data-field="<?php p($fieldName)?>"
          <?php
            p(!$isCreationMode && array_key_exists($fieldName, $provider) && $provider[$fieldName] ? 'checked' : '');
            print_unescaped(' data-provider="'.$k.'"');
            p($fieldData['required'] ? ' required' : '');
          ?>
        />
        <label for="<?php p($provType)?>_providers[<?php p($k) ?>][<?php p($fieldName)?>]">
          <?php p($l->t($fieldData['title'])) ?>
        </label>
    </div>
  <?php endif ?>
  <?php if($fieldName === "attributeMapping"): ?>
    <div class="sociallogin-detail-options <?php p($isCreationMode || !array_key_exists($fieldName, $provider) || !$provider[$fieldName] ? 'hidden' : '')?>" data-field="<?php p($fieldName) ?>" data-provider="<?php p($k) ?>">
      <h3><?php p($l->t("Attribute mappings"))?><h3>
      <table class="grid">
        <thead>
          <tr>
            <th><?php p($l->t("old name"))?></th>
            <th><?php p($l->t("new name"))?></th>
          </tr>
        <thead>
        <tbody data-field="<?php p($fieldName) ?>" data-provider="<?php p($k) ?>">
          <?php if(!$isCreationMode && array_key_exists($fieldName, $provider) && is_array($provider[$fieldName])): ?>
            <?php foreach ($provider[$fieldName]["original_attribute"] as $key => $origAttr):?>
              <tr>
                <td><input type="text" name="<?php p($provType)?>_providers[<?php p($k) ?>][<?php p($fieldName)?>][original_attribute][]" placeholder="<?php p($l->t("old name"))?>" data-provider="<?php p($k) ?>" value="<?php p($origAttr) ?>"></td>
                <td><input type="text" name="<?php p($provType)?>_providers[<?php p($k) ?>][<?php p($fieldName)?>][new_attribute][]" placeholder="<?php p($l->t("new name"))?>" data-provider="<?php p($k) ?>"  value="<?php p($provider[$fieldName]["new_attribute"][$key]) ?>"></td>
                <td><div class="icon-delete" data-field="<?php p($fieldName) ?>"  data-action="delete" data-provider="<?php p($k) ?>"></div></td>
              </tr>
            <?php endforeach ?>
          <?php endif ?>
          <tr data-action="new">
            <td><input type="text" name="original_attribute" placeholder="<?php p($l->t("old name"))?>" data-provider="<?php p($k) ?>"></td>
            <td><input type="text" name="new_attribute" placeholder="<?php p($l->t("new name"))?>" data-provider="<?php p($k) ?>"></td>
            <td><button type="button" data-field="<?php p($fieldName) ?>" data-provider="<?php p($k) ?>" data-provider-type="<?php p($provType)?>">
                <div class="icon-add"></div>
            </button></td>
          </tr>
        </tbody>
      </table>
    </div>
  <?php endif ?>
<?php endforeach ?>
<label>
    <?php p($l->t('Button style')) ?><br>
    <select name="<?php p($provType) ?>_providers[<?php p($k) ?>][style]">
        <option value=""><?php p($l->t('None')); ?></option>
        <?php foreach ($styleClass as $style => $styleTitle): ?>
            <option value="<?php p($style) ?>" <?php p(!$isCreationMode && isset($provider['style']) && $provider['style'] === $style ? 'selected' : '') ?>>
                <?php p($styleTitle) ?>
            </option>
        <?php endforeach ?>
    </select>
</label>
<br/>
<label>
    <?php p($l->t('Default group')) ?><br>
    <select name="<?php p($provType) ?>_providers[<?php p($k) ?>][defaultGroup]">
        <option value=""><?php p($l->t('None')); ?></option>
        <?php foreach ($_['groups'] as $group): ?>
            <option value="<?php p($group) ?>" <?php p(!$isCreationMode && isset($provider['defaultGroup']) && $provider['defaultGroup'] === $group ? 'selected' : '') ?>>
                <?php p($group) ?>
            </option>
        <?php endforeach ?>
    </select>
</label>
<br/>
<?php if (in_array($provType, ['custom_oidc', 'custom_oauth2'])): ?>
    <button class="group-mapping-add" type="button"><?php p($l->t('Add group mapping')) ?></button>
    <div class="group-mapping-tpl">
        <input type="text" class="foreign-group" data-name-tpl="<?php p($provType) ?>_providers[<?php p($k) ?>][groupMapping]"  />
        <select class="local-group">
            <?php foreach ($_['groups'] as $group): ?>
                <option value="<?php p($group) ?>"><?php p($group) ?></option>
            <?php endforeach ?>
        </select>
        <span class="group-mapping-remove">x</span>
    </div>
    <?php if (!$isCreationMode && isset($provider['groupMapping']) && is_array($provider['groupMapping'])): ?>
        <?php foreach ($provider['groupMapping'] as $foreignGroup => $localGroup): ?>
            <div>
                <input type="text" class="foreign-group" value="<?php p($foreignGroup) ?>"
                    data-name-tpl="<?php p($provType) ?>_providers[<?php p($k) ?>][groupMapping]"
                />
                <select class="local-group"
                    name="<?php p($provType) ?>_providers[<?php p($k) ?>][groupMapping][<?php p($foreignGroup) ?>]"
                >
                    <?php foreach ($_['groups'] as $group): ?>
                        <option value="<?php p($group) ?>" <?php p($localGroup === $group ? 'selected' : '') ?>>
                            <?php p($group) ?>
                        </option>
                    <?php endforeach ?>
                </select>
                <span class="group-mapping-remove">x</span>
            </div>
        <?php endforeach ?>
    <?php endif ?>
    <?php if($isCreationMode): ?>
      <div id="sociallogin-<?php p($provType)?>-attributeMapping-tpl" class="sociallogin_tpl">
        <input type="text" name="<?php p($provType)?>_providers[<?php p($k) ?>][attributeMapping][original_attribute][]" placeholder="<?php p($l->t("old name"))?>" value="{{oa_value}}" data-provider="<?php p($k) ?>">
        <input type="text" name="<?php p($provType)?>_providers[<?php p($k) ?>][attributeMapping][new_attribute][]" placeholder="<?php p($l->t("new name"))?>" value="{{na_value}}" data-provider="<?php p($k) ?>">
        <div class="icon-delete" data-field="attributeMapping" data-action="delete" data-provider="<?php p($k) ?>"></div>
      </div>
    <?php endif ?>
<?php endif ?>
