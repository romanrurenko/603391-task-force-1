<?php

namespace frontend\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "city".
 *
 * @property int $id
 * @property string $city
 * @property float $lat
 * @property float $lng
 *
 * @property Profile[] $profiles
 */
class City extends ActiveRecord
{
    use ExceptionOnFindFail;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'city';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['city', 'lat', 'lng'], 'required'],
            [['lat', 'lng'], 'number'],
            [['city'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'city' => 'City',
            'lat' => 'Lat',
            'lng' => 'Lng',
        ];
    }

    /**
     * Gets query for [[Profiles]].
     * @return ActiveQuery|ProfileQuery
     */
    public function getProfiles()
    {
        return $this->hasMany(Profile::class, ['city_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return CityQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CityQuery(get_called_class());
    }

    /**
     * Return array cities
     * @return array.
     */
    public static function getList(): array
    {
        return ArrayHelper::map(City::find()->asArray()->all(), 'id', 'city');
    }

    /**
     * @param string|null $city
     * @return int|null
     */
    public static function findIdByName(?string $city): ?int
    {
        return City::find()->select('id')->where(['city' => $city])->limit(1)->one()['id'] ?? null;
    }
}
