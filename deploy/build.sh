set -e

dir=$(pwd)

rm -rf ${dir}/build
mkdir ${dir}/build
cd ${dir}/build
git clone git@github.com:extraton/depools-dashboard-and-staking.git .
git checkout "${1}"
cp ${dir}/.env.prod ./.env
sed -i '' -e "s/__APP_VERSION__/${1}/" ./.env
cd front
yarn install
yarn run build
cd ${dir}/build
cp -R ${dir}/build/front/dist/* ${dir}/build/public/
rm -rf ./{.git,front,deploy}
docker build -f ../deploy/Dockerfile -t extraton-depool:${1} .
